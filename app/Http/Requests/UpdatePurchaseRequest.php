<?php

namespace App\Http\Requests;

use App\Models\TruckFruit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $purchaseId = $this->route('purchase')->id ?? null;

        return [
            'customer_id' => [
                'nullable',
                'exists:customers,id',
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
        return (new StorePurchaseRequest())->messages();
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            /*
             * ==========================================================
             * DUPLICATE CUSTOMER PURCHASE CHECK
             * ==========================================================
             *
             * Only check this when a real customer is selected.
             *
             * NULL customer = cash purchase, so duplicates are allowed.
             */

            if ($this->filled('customer_id')) {

                $purchaseId = $this->route('purchase')->id ?? null;

                $query = DB::table('customer_purchase')
                    ->where('customer_id', $this->customer_id)
                    ->where('truck_fruit_id', $this->truck_fruit_id)
                    ->where('box_class', $this->box_class)
                    ->where('date', $this->date)
                    ->where('id', '!=', $purchaseId);

                /*
                 * Same UNIT price OR same BOX price.
                 */
                $query->where(function ($query) {

                    if ($this->filled('unique_unit_price')) {
                        $query->where(
                            'unique_unit_price',
                            $this->unique_unit_price
                        );
                    }

                    if ($this->filled('unique_box_price')) {
                        $query->orWhere(
                            'unique_box_price',
                            $this->unique_box_price
                        );
                    }
                });

                if ($query->exists()) {
                    $validator->errors()->add(
                        'truck_fruit_id',
                        'لا يمكن للزبون تسجيل نفس المشتروات بنفس الفئة والسعر في نفس اليوم.'
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
                 * Weight required ONLY if truck has no unified weight
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
