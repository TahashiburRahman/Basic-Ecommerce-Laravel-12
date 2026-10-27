@extends('admin.layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-link p-0 text-decoration-none">Back to dashboard</a>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Add category</a>
</div>

<div class="form-panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td><img src="{{ $category->image_url }}" alt="{{ $category->name }}" width="60" height="60" loading="lazy" style="object-fit:cover;border-radius:8px;"></td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td><span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No categories available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $categories->links() }}</div>
</div>
@endsection
