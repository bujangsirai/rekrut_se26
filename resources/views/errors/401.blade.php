@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Anda belum melakukan login. Silakan masuk terlebih dahulu untuk melanjutkan.'))
