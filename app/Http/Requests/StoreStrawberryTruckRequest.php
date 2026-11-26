<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStrawberryTruckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Truck fields
            'date' => ['required', 'date'],
            'total_boxes' => ['required', 'integer'],
            'freight' => ['required', 'integer'],
            'driver_id' => ['nullable', 'exists:drivers,id'],

            // Strawberry varieties
            'fruits' => ['required', 'array', 'min:1'],
            'fruits.*.fruit_type' => [
                'required',
                'in:فلوريدا,إستفالة,فرتونة,فراولة',
            ],
            'fruits.*.client_id' => ['nullable', 'exists:clients,id'],
            'fruits.*.box_type' => [
                'required',
                'in:normal_box,big_box,small_box,small_net',
            ],
            'fruits.*.number_of_boxes' => ['required', 'integer'],
            'fruits.*.second_class_boxes' => ['nullable', 'integer'],
            'fruits.*.third_class_boxes' => ['nullable', 'integer'],
            'fruits.*.unified_weight' => ['nullable', 'numeric'],
            'fruits.*.unified_unit_price' => ['nullable', 'numeric'],
            'fruits.*.unified_box_price' => ['nullable', 'integer'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $fruits = $this->input('fruits', []);
            $totalBoxes = (int) $this->input('total_boxes', 0);

            // 1. Validate total boxes.
            $sum = collect($fruits)->sum(
                fn($fruit) =>
                isset($fruit['number_of_boxes'])
                    ? (int) $fruit['number_of_boxes']
                    : 0
            );

            if ($sum !== $totalBoxes) {
                $validator->errors()->add(
                    'total_boxes',
                    'مجموع عدد الصناديق في الأصناف يجب أن يساوي إجمالي عدد الصناديق للعربة.'
                );
            }

            $combinations = [];

            foreach ($fruits as $index => $fruit) {

                // 2. Prevent duplicate fruit_type + box_type combinations.
                if (!isset($fruit['fruit_type'], $fruit['box_type'])) {
                    continue;
                }

                $key = $fruit['fruit_type'] . '-' . $fruit['box_type'];

                if (isset($combinations[$key])) {
                    $validator->errors()->add(
                        "fruits.$index.fruit_type",
                        'لا يمكن تكرار نفس الصنف مع نفس نوع البرنيكة مرتين.'
                    );
                }

                $combinations[$key] = true;

                // 3. Validate unified pricing.
                $unit = $fruit['unified_unit_price'] ?? null;
                $box = $fruit['unified_box_price'] ?? null;
                $weight = $fruit['unified_weight'] ?? null;

                if (empty($unit) && empty($box)) {
                    $validator->errors()->add(
                        "fruits.$index.unified_unit_price",
                        'يجب إدخال سعر الكيلو أو سعر البرنيكة.'
                    );

                    $validator->errors()->add(
                        "fruits.$index.unified_box_price",
                        'يجب إدخال سعر الكيلو أو سعر البرنيكة.'
                    );
                }

                if (!empty($unit) && !empty($box)) {
                    $validator->errors()->add(
                        "fruits.$index.unified_unit_price",
                        'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.'
                    );

                    $validator->errors()->add(
                        "fruits.$index.unified_box_price",
                        'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.'
                    );
                }

                // 4. A box price requires a unified box weight.
                if (!empty($box) && empty($weight)) {
                    $validator->errors()->add(
                        "fruits.$index.unified_weight",
                        'يرجى إدخال الوزن الموحد للبرنيكة.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'date.required' => 'التاريخ مطلوب.',
            'total_boxes.required' => 'إجمالي عدد الصناديق مطلوب.',
            'freight.required' => 'النولون مطلوب.',
            'driver_id.exists' => 'السائق المحدد غير موجود.',

            'fruits.required' => 'يجب إضافة صنف واحد على الأقل.',
            'fruits.min' => 'يجب إضافة صنف واحد على الأقل.',

            'fruits.*.fruit_type.required' => 'اختر الصنف.',
            'fruits.*.fruit_type.in' => 'الصنف المحدد غير صالح.',
            'fruits.*.client_id.exists' => 'العميل المحدد غير موجود.',
            'fruits.*.box_type.required' => 'اختر نوع البرنيكة.',
            'fruits.*.box_type.in' => 'نوع البرنيكة غير صالح.',
            'fruits.*.number_of_boxes.required' => 'أدخل عدد الصناديق.',
        ];
    }
}
