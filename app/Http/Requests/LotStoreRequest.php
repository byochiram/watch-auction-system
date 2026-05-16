<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use App\Models\AuctionLot;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LotStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    private function parseLocal($val): Carbon
    {
        // input datetime-local: 2026-02-21T10:21
        return Carbon::createFromFormat('Y-m-d\TH:i', $val, config('app.timezone'))
            ->setTimezone(config('app.timezone'))
            ->startOfMinute();
    }

    protected function failedValidation(Validator $validator)
    {
        session()->flash('reopen_create', true);

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'exists:products,id',
                function ($attr, $val, $fail) {
                    $start = $this->input('start_at');
                    $end   = $this->input('end_at');
                    if (!$start || !$end) return;

                    $overlap = AuctionLot::where('product_id', $val)
                        ->active()
                        ->where(function ($q) use ($start, $end) {
                            $q->whereBetween('start_at', [$start, $end])
                              ->orWhereBetween('end_at', [$start, $end])
                              ->orWhere(function ($qq) use ($start, $end) {
                                  $qq->where('start_at', '<=', $start)
                                     ->where('end_at', '>=', $end);
                              });
                        })
                        ->exists();

                    if ($overlap) {
                        $fail('Produk ini sudah terjadwal/berjalan pada rentang waktu tersebut.');
                    }
                },
            ],

            'start_price' => ['required', 'numeric', 'min:0'],
            'increment'   => ['required', 'numeric', 'min:0.01'],

            'start_at' => [
            'required','date',
                function ($attr, $val, $fail) {
                    $start  = $this->parseLocal($val);
                    $nowMin = now(config('app.timezone'))->startOfMinute();

                    if ($start->lt($nowMin)) {
                        $fail('Waktu mulai tidak boleh sebelum waktu saat ini.');
                    }
                }
            ],
            'end_at' => [
                'required','date',
                function ($attr, $val, $fail) {
                    $startVal = $this->input('start_at');
                    if (!$startVal) return;

                    $start  = $this->parseLocal($startVal);
                    $end    = $this->parseLocal($val);
                    $nowMin = now(config('app.timezone'))->startOfMinute();

                    if ($end->lte($start)) {
                        $fail('Waktu selesai harus setelah waktu mulai.');
                        return;
                    }

                    if ($end->lte($nowMin)) {
                        $fail('Waktu selesai harus setelah waktu saat ini.');
                        return;
                    }
                }
            ],
        ];
    }
}