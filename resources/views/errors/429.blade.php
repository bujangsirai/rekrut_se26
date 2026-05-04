@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Permintaan Anda terlalu banyak dalam waktu singkat. Silakan coba kembali beberapa saat lagi.'))
