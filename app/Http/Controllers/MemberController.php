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
        return view('members.create');
    }

    public function store(Request $request)
    {
        // build validation rules defensively to avoid querying non-existent columns
        $rules = [
            'nama' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'telepon' => 'required',
            'alamat' => 'required',
        ];

        if (Schema::hasColumn('members', 'kode_anggota')) {
            $rules['kode_anggota'] = 'required|unique:members,kode_anggota';
        } else {
            // fallback: accept kode_anggota but don't run unique check
            $rules['kode_anggota'] = 'required';
        }

        if (Schema::hasColumn('members', 'email')) {
            $rules['email'] = 'required|email|unique:members,email';
        }

        $request->validate($rules);

        Member::create($request->all());

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Data anggota berhasil ditambahkan.');
    }

    public function show(Member $member)
    {
        //
    }

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {

        $rules = [
            'nama' => 'required',
            'email' => 'required|email',
            'telepon' => 'required',
            'alamat' => 'required',
            'password' => 'nullable|min:6',
        ];

        if (Schema::hasColumn('members', 'kode_anggota')) {
            $rules['kode_anggota'] = 'required|unique:members,kode_anggota,' . $member->id;
        } else {
            $rules['kode_anggota'] = 'required';
        }

        if (Schema::hasColumn('members', 'email')) {
            $rules['email'] = 'required|email|unique:members,email,' . $member->id;
        }

        $request->validate($rules);

        $data = $request->all();
        
        // Only update password if provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $member->update($data);

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Data anggota berhasil dihapus.');
    }
}