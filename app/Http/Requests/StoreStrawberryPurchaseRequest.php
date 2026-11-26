<?php

namespace App\Http\Requests;

use App\Models\StrawberryTruckFruit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StoreStrawberryPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'nullable',
                'exists:customers,id',
            ],

            'date' => [
                'required',
                'date',
            ],

            'strawberry_truck_fruit_id' => [
                'required',
                'exists:strawberry_truck_fruits,id',
            ],

            'box_class' => [
                'required',
                Rule::in(['first', 'second', 'third']),
            ],

            'number_of_boxes' => [
                'required',
                'integer',
                'min:1',
            ],

            'total_weight' => [
                'nullable',
                'numeric',
                'min:1',
            ],

            'unique_unit_price' => [
                'nullable',
                'numeric',
                'min:1',
            ],

            'unique_box_price' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'الزبون المحدد غير موجود.',

            'date.required' => 'يرجى إدخال التاريخ.',
            'date.date' => 'التاريخ غير صحيح.',

            'strawberry_truck_fruit_id.required' => 'اختر صنف الفراولة.',
            'strawberry_truck_fruit_id.exists' => 'الصنف المحدد غير موجود.',

            'box_class.required' => 'يرجى اختيار فئة المشتروات.',
            'box_class.in' => 'فئة المشتروات غير صحيحة.',

            'number_of_boxes.required' => 'عدد البرانيك مطلوب.',
            'number_of_boxes.integer' => 'عدد البرانيك غير صحيح.',
            'number_of_boxes.min' => 'عدد البرانيك يجب أن يكون 1 على الأقل.',

            'total_weight.numeric' => 'إجمالي الوزن غير صحيح.',
            'total_weight.min' => 'إجمالي الوزن غير صحيح.',

            'unique_unit_price.numeric' => 'سعر الكيلو غير صحيح.',
            'unique_unit_price.min' => 'سعر الكيلو غير صحيح.',

            'unique_box_price.integer' => 'سعر البرنيكة غير صحيح.',
            'unique_box_price.min' => 'سعر البرنيكة غير صحيح.',
        ];
    }

    /**
     * Validate duplicate purchases and Strawberry pricing rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            /*
            |--------------------------------------------------------------------------
            | 1. Duplicate customer purchase check
            |--------------------------------------------------------------------------
            */

            if ($this->filled('customer_id')) {
                $purchase = $this->route('purchase');
                $purchaseId = $purchase?->id;

                $query = DB::table('strawberry_customer_purchases')
                    ->where('customer_id', $this->customer_id)
                    ->where(
                        'strawberry_truck_fruit_id',
                        $this->strawberry_truck_fruit_id
                    )
                    ->where('box_class', $this->box_class)
                    ->whereDate('date', $this->date);

                if ($purchaseId !== null) {
                    $query->where('id', '!=', $purchaseId);
                }

                if ($query->exists()) {
                    $validator->errors()->add(
                        'strawberry_truck_fruit_id',
                        'لا يمكن تسجيل نفس العميل بنفس الصنف والفئة مرتين في نفس اليوم.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Retrieve the selected Strawberry truck variety
            |--------------------------------------------------------------------------
            */

            $truckFruit = StrawberryTruckFruit::find(
                $this->strawberry_truck_fruit_id
            );

            if (!$truckFruit) {
                return;
            }

            $unitDefined = $truckFruit->unified_unit_price !== null;
            $boxDefined = $truckFruit->unified_box_price !== null;

            $unit = $this->input('unique_unit_price');
            $box = $this->input('unique_box_price');
            $weight = $this->input('total_weight');

            $hasUnit = $unit !== null && $unit !== '';
            $hasBox = $box !== null && $box !== '';
            $hasWeight = $weight !== null && $weight !== '';

            /*
            |--------------------------------------------------------------------------
            | 3. Cannot enter both custom prices
            |--------------------------------------------------------------------------
            */

            if ($hasUnit && $hasBox) {
                $validator->errors()->add(
                    'unique_unit_price',
                    'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.'
                );

                $validator->errors()->add(
                    'unique_box_price',
                    'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Unit-priced truck variety
            |--------------------------------------------------------------------------
            |
            | A custom box price is allowed and overrides unit pricing.
            | Otherwise, actual weight is required.
            */

            if ($unitDefined) {
                if (!$hasBox && !$hasWeight) {
                    $validator->errors()->add(
                        'total_weight',
                        'يجب تحديد الوزن عند استخدام سعر الكيلو.'
                    );
                }

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Box-priced truck variety
            |--------------------------------------------------------------------------
            |
            | Custom unit price activates the special calculation:
            | actual weight * custom unit price.
            |
            | Actual weight is required for that calculation.
            */

            if ($boxDefined && $hasUnit && !$hasWeight) {
                $validator->errors()->add(
                    'total_weight',
                    'يجب تحديد الوزن الفعلي عند استخدام سعر الكيلو الخاص.'
                );
            }
        });
    }

    /**
     * Return JSON validation errors for AJAX requests.
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
