<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'libelle' => [
                'required',
                'max:255',
                Rule::unique('articles', 'libelle'),
            ],
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }
    public function messages()
    {
        return [
            'libelle.unique' => 'Un article avec ce libellé existe déjà.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->all())) {
                $validator->errors()->add('articles', 'Au moins un article est requis');
            }
        });
    }
}
