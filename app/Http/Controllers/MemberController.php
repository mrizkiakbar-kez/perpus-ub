<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::latest()->get();

        return view('members.index', compact('members'));
    }

    public function create()
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function store(Request $request)
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function show(Member $member)
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function edit(Member $member)
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function update(Request $request, Member $member)
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function destroy(Member $member)
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }
}