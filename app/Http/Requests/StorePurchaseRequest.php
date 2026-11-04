<?php

namespace App\Http\Requests;

use App\Models\CustomerPurchase;
use App\Models\TruckFruit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
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
                'exists:customers,id'
            ],

            'date' => [
                'required',
                'date',
            ],

            'truck_fruit_id' => [
                'required',
                'exists:truck_fruit,id',
            ],

            'box_class' => [
                'required',
                Rule::in(['first', 'second', 'third']),
            ],

            'number_of_boxes' => [
                'required',
                'integer',
                'min:1'
            ],

            'total_weight' => [
                'nullable',
                'numeric',
                'min:1'
            ],

            'unique_unit_price' => [
                'nullable',
                'numeric',
                'min:1'
            ],

            'unique_box_price' => [
                'nullable',
                'integer',
                'min:1'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'الزبون المحدد غير موجود.',

            'date.required' => 'يرجى إدخال التاريخ.',
            'date.date' => 'التاريخ غير صحيح.',

            'truck_fruit_id.required' => 'اختر الصنف.',
            'truck_fruit_id.exists' => 'الصنف المحدد غير موجود.',
            'truck_fruit_id.unique' => 'لا يمكن تسجيل نفس العميل بنفس المشتروات مرتين.',

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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if ($this->filled('customer_id')) {

                $query = CustomerPurchase::query()
                    ->where('customer_id', $this->customer_id)
                    ->where('truck_fruit_id', $this->truck_fruit_id)
                    ->where('box_class', $this->box_class)
                    ->whereDate('date', $this->date)
                    ->where(function ($query) {

                        // Same unit price
                        if (!is_null($this->unique_unit_price)) {
                            $query->where('unique_unit_price', $this->unique_unit_price);
                        }

                        // OR same box price
                        if (!is_null($this->unique_box_price)) {
                            $query->orWhere('unique_box_price', $this->unique_box_price);
                        }
                    });

                if ($query->exists()) {
                    $validator->errors()->add(
                        'truck_fruit_id',
                        'لا يمكن تسجيل نفس العميل بنفس المشتروات والسعر مرتين في نفس اليوم.'
                    );
                }
            }
            $truckFruit = TruckFruit::find($this->truck_fruit_id);

            if (!$truckFruit) {
                return;
            }

            $unitDefined = $truckFruit->unified_unit_price;
            $boxDefined  = $truckFruit->unified_box_price;

            $unit  = $this->unique_unit_price;
            $box   = $this->unique_box_price;
            $total = $this->total_weight;

            /**
             * Cannot enter both custom prices
             */
            if (!is_null($unit) && !is_null($box)) {
                $validator->errors()->add(
                    'unique_unit_price',
                    'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.'
                );

                $validator->errors()->add(
                    'unique_box_price',
                    'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.'
                );
            }

            /**
             * If custom unit price exists → weight required
             */
            if (!is_null($unit) && is_null($total)) {
                $validator->errors()->add(
                    'total_weight',
                    'عند إدخال سعر الكيلو يجب تحديد الوزن.'
                );
            }

            /**
             * Truck uses unit-price mode
             */
            if (!is_null($unitDefined)) {
                if (!is_null($box)) {
                    $validator->errors()->add(
                        'unique_box_price',
                        'لا يمكن إدخال سعر البرنيكة لأن الصنف يعمل بسعر الكيلو.'
                    );
                }

                /**
                 * Weight required ONLY if no unified weight exists
                 */
                if (is_null($total) && is_null($truckFruit->unified_weight)) {
                    $validator->errors()->add(
                        'total_weight',
                        'يجب تحديد الوزن.'
                    );
                }
            }

            /**
             * Truck uses box-price mode
             */
            if (!is_null($boxDefined)) {
                if (!is_null($unit)) {
                    $validator->errors()->add(
                        'unique_unit_price',
                        'لا يمكن إدخال سعر الكيلو لأن الصنف يعمل بسعر البرنيكة.'
                    );
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
