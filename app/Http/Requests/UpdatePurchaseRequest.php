<?php

namespace App\Http\Requests;

use App\Models\TruckFruit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Assuming the route has {purchase} parameter
        $purchaseId = $this->route('purchase')->id ?? null;

        return [
            'customer_id'        => ['required', 'exists:customers,id'],
            'date'               => ['required'],
            'truck_fruit_id' => [
                'required',
                'exists:truck_fruit,id',
                Rule::unique('customer_purchase')
                    ->ignore($purchaseId) // ignore current record
                    ->where(function ($query) {
                        return $query
                            ->where('customer_id', $this->customer_id)
                            ->where('truck_fruit_id', $this->truck_fruit_id)
                            ->where('box_class', $this->box_class)
                            ->where('date', $this->date);
                    }),
            ],
            'box_class'          => ['required'],
            'number_of_boxes'    => ['required', 'integer', 'min:1'],
            'total_weight'       => ['nullable', 'numeric', 'min:1'],
            'unique_unit_price'  => ['nullable', 'numeric', 'min:1'],
            'unique_box_price'   => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Customize validation messages.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return (new StorePurchaseRequest())->messages();
    }

    /**
     * Customized rules for unique prices
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $truckFruit = TruckFruit::find($this->truck_fruit_id);

            if (!$truckFruit) return;

            $unitDefined = $truckFruit->unified_unit_price;
            $boxDefined  = $truckFruit->unified_box_price;

            $unit  = $this->unique_unit_price;
            $box   = $this->unique_box_price;
            $total = $this->total_weight;

            /**
             * Cannot enter both
             */
            if (!empty($unit) && !empty($box)) {
                $validator->errors()->add('unique_unit_price', 'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.');
                $validator->errors()->add('unique_box_price', 'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.');
            }

            /**
             * If Unit price chosen → weight required
             */
            if (!empty($unit) && empty($total)) {
                $validator->errors()->add('total_weight', 'عند إدخال سعر الكيلو، يجب تحديد إجمالي الوزن.');
            }

            /**
             * Enforce truck mode:
             *    If truck uses unit price → customer MUST use unit
             *    If truck uses box  price → customer MUST use box
             */
            if ($unitDefined) {
                // ❌ Wrong: box entered while truck uses unit price
                if (!empty($box)) {
                    $validator->errors()->add('unique_box_price', 'لا يمكن إدخال سعر البرنيكة لأن الصنف يعمل بسعر الكيلو.');
                }
                if (empty($total)) {
                    $validator->errors()->add('total_weight', 'يجب تحديد إجمالي الوزن.');
                }
            }

            if ($boxDefined) {
                // ❌ Wrong: unit entered while truck uses box price
                if (!empty($unit)) {
                    $validator->errors()->add('unique_unit_price', 'لا يمكن إدخال سعر الكيلو لأن الصنف يعمل بسعر البرنيكة.');
                }
                if (!empty($total)) {
                    $validator->errors()->add('total_weight', 'لا يمكن إدخال إجمالى الوزن لان السعر موحد بالبرنيكة');
                }
            }
        });
    }


    /**
     * Customize validation messages.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
