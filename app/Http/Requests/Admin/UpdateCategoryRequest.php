<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends CategoryRequest
{
    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $validator->after(function (Validator $validator): void {
            $category = $this->category();
            $parentId = $this->integer('parent_id');

            if (! $category instanceof Category || $parentId === 0) {
                return;
            }

            if ($parentId === $category->id || in_array($parentId, app(CategoryService::class)->descendantIds($category), true)) {
                $validator->errors()->add('parent_id', __('admin.messages.invalid_category_parent'));
            }
        });
    }
}
