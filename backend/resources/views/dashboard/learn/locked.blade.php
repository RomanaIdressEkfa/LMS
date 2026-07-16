@extends('layouts.dashboard')
@section('title', $course->title . ' — LMS')

@section('content')
<div class="card mx-auto max-w-lg grid place-items-center p-12 text-center">
    <span class="text-4xl">🔒</span>
    <p class="mt-3 font-bold">Enroll to access this course.</p>
    <a href="/courses/{{ $course->slug }}" class="btn-primary mt-4">View course</a>
</div>
@endsection
