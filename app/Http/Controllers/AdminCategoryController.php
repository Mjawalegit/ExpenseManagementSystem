<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpenseCategory::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->orderBy('name')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        ExpenseCategory::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(ExpenseCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, ExpenseCategory $category)
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(ExpenseCategory $category)
    {
        $expenseCount = $category->expenses()->count();

        if ($expenseCount > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete category with existing expenses.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
