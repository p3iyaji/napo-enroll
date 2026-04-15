<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollementController extends Controller
{
    public function create()
    {
        $courses = Course::select('id', 'name')->get();
        $users = User::select('id', 'name', 'email')->get();

        return Inertia::render('Enrollment/Create', compact('courses', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'required|date',
        ]);

        $validated['status'] = 'active';

        $enrollment = Enrollment::create($validated);

        if ($request->expectsJson()) {
            return response()->json($enrollment->only(['id', 'user_id', 'course_id', 'enrollment_date', 'status']));
        }

        return redirect()->route('enrollments')->with('success', 'Enrollment created successfully');
    }
}
