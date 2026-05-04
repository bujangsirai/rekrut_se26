<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleDriveService
{
    public function makeClient(): Client
    {
        $client = new Client;

        $client->setClientId((string) config('services.google.client_id'));
        $client->setClientSecret((string) config('services.google.client_secret'));
        $client->setRedirectUri((string) config('services.google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);
        $client->setScopes([
            Drive::DRIVE,
        ]);

        return $client;
    }

    public function makeAuthorizedDrive(): Drive
    {
        $client = $this->makeClient();
        $client->setAccessToken([
            'access_token' => $this->getAccessToken(),
        ]);

        return new Drive($client);
    }

    public function listFiles(int $pageSize = 20, ?string $pageToken = null): array
    {
        $drive = $this->makeAuthorizedDrive();
        $folderId = $this->getFolderId();

        $response = $drive->files->listFiles([
            'q' => sprintf("'%s' in parents and trashed = false", addslashes($folderId)),
            'orderBy' => 'modifiedTime desc',
            'pageSize' => $pageSize,
            'pageToken' => $pageToken,
            'fields' => 'nextPageToken, files(id, name, mimeType, size, webViewLink, webContentLink, createdTime, modifiedTime)',
        ]);

        return [
            'items' => array_map(
                fn (DriveFile $file): array => $this->formatFile($file),
                $response->getFiles()
            ),
            'next_page_token' => $response->getNextPageToken(),
        ];
    }

    public function uploadFile(UploadedFile $uploadedFile, ?string $name = null, bool $makePublic = false): array
    {
        $drive = $this->makeAuthorizedDrive();

        $fileMetadata = new DriveFile([
            'name' => $name ?: $uploadedFile->getClientOriginalName(),
            'parents' => [$this->getFolderId()],
        ]);

        $createdFile = $drive->files->create($fileMetadata, [
            'data' => file_get_contents($uploadedFile->getRealPath()),
            'mimeType' => $uploadedFile->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id, name, mimeType, size, webViewLink, webContentLink, createdTime, modifiedTime',
        ]);

        if ($makePublic) {
            $this->makeFilePublic($createdFile->getId());

            $createdFile = $drive->files->get($createdFile->getId(), [
                'fields' => 'id, name, mimeType, size, webViewLink, webContentLink, createdTime, modifiedTime',
            ]);
        }

        return $this->formatFile($createdFile);
    }

    public function renameFile(string $fileId, string $name): array
    {
        $drive = $this->makeAuthorizedDrive();
        $updatedFile = $drive->files->update($fileId, new DriveFile([
            'name' => $name,
        ]), [
            'fields' => 'id, name, mimeType, size, webViewLink, webContentLink, createdTime, modifiedTime',
        ]);

        return $this->formatFile($updatedFile);
    }

    public function deleteFile(string $fileId): void
    {
        $drive = $this->makeAuthorizedDrive();
        $drive->files->delete($fileId);
    }

    public function makeFilePublic(string $fileId): void
    {
        $drive = $this->makeAuthorizedDrive();
        $permission = new Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);

        $drive->permissions->create($fileId, $permission, [
            'fields' => 'id',
        ]);
    }

    protected function getFolderId(): string
    {
        $folderId = (string) config('services.google.drive_folder_id');

        if ($folderId === '') {
            throw new HttpException(500, 'Google Drive folder ID is not configured.');
        }

        return $folderId;
    }

    protected function getAccessToken(): string
    {
        $cacheStore = Cache::store((string) config('services.google.cache_store'));
        $cacheKey = (string) config('services.google.access_token_cache_key');
        $lockKey = (string) config('services.google.access_token_lock_key');
        $cachedAccessToken = $cacheStore->get($cacheKey);

        if (is_string($cachedAccessToken) && $cachedAccessToken !== '') {
            return $cachedAccessToken;
        }

        return $cacheStore->lock($lockKey, 10)->block(5, function () use ($cacheStore, $cacheKey): string {
            $cachedAccessToken = $cacheStore->get($cacheKey);

            if (is_string($cachedAccessToken) && $cachedAccessToken !== '') {
                return $cachedAccessToken;
            }

            $refreshToken = (string) config('services.google.refresh_token');

            if ($refreshToken === '') {
                throw new HttpException(500, 'Google Drive refresh token is not configured.');
            }

            $client = $this->makeClient();
            $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (! is_array($token) || isset($token['error'])) {
                throw new HttpException(500, 'Failed to refresh Google Drive access token.');
            }

            $accessToken = $token['access_token'] ?? null;

            if (! is_string($accessToken) || $accessToken === '') {
                throw new HttpException(500, 'Google Drive access token was not returned.');
            }

            $expiresIn = max(60, ((int) ($token['expires_in'] ?? 3600)) - 60);

            $cacheStore->put($cacheKey, $accessToken, now()->addSeconds($expiresIn));

            return $accessToken;
        });
    }

    protected function formatFile(DriveFile $file): array
    {
        return [
            'id' => $file->getId(),
            'name' => $file->getName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'web_view_link' => $file->getWebViewLink(),
            'web_content_link' => $file->getWebContentLink(),
            'created_at' => $file->getCreatedTime(),
            'updated_at' => $file->getModifiedTime(),
        ];
    }
}
