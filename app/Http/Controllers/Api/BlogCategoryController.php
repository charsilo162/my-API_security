<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Http\Resources\CategoryResource;

class BlogCategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(
            BlogCategory::all()
        );
    }
}