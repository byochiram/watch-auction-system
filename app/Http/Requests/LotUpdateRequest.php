<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use App\Models\AuctionLot;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LotUpdateRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return true; 
    }

    protected function failedValidation(Validator $validator)
    {
        $lot = $this->route('lot');

        session()->flash('reopen_edit', [
            'id'            => $lot->id,
            'product_id'    => $lot->product_id,
            'start_price'   => $lot->start_price,
            'increment'     => $lot->increment,
            'start_at'      => optional($lot->start_at)->format('Y-m-d\TH:i'),
            // pakai input user kalau ada
            'end_at'        => old('end_at') ?? optional($lot->end_at)->format('Y-m-d\TH:i'),
            'runtime_status'=> $lot->runtime_status,
        ]);

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }

    protected function prepareForValidation()
    {
        // hapus titik pemisah ribuan sebelum divalidasi
        foreach (['start_price','increment'] as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => preg_replace('/[^\d]/', '', $this->$field),
                ]);
            }
        }
    }

    public function rules(): array
    {
        /** @var \App\Models\AuctionLot|null $lot */
        $lot    = $this->route('lot');
        $status = $lot?->runtime_status;
        $id     = $lot?->id;

        if ($status === 'ACTIVE') {
            return [
                'end_at' => [
                    'required', 'date',
                    function ($attr, $val, $fail) use ($lot) {
                        $newEnd = Carbon::parse($val);

                        // konsisten dengan datetime-local (menit)
                        $nowMin = now()->startOfMinute();

                        // 1) wajib setelah waktu mulai
                        if ($newEnd->lessThanOrEqualTo($lot->start_at)) {
                            $fail('Waktu selesai harus setelah waktu mulai.');
                            return;
                        }

                        // 2) wajib setelah waktu saat ini
                        if ($newEnd->lessThanOrEqualTo($nowMin)) {
                            $fail('Waktu selesai harus setelah waktu saat ini.');
                            return;
                        }
                    },
                ],
            ];
        }

        // SCHEDULED (atau lainnya) → aturan penuh seperti sebelumnya
        return [
            'product_id'  => ['required','exists:products,id',
                function($attr,$val,$fail) use ($id){
                    $overlap = AuctionLot::where('product_id',$val)
                        ->where('id','!=',$id)
                        ->active()
                        ->where(function($q){
                            $q->whereBetween('start_at',[request('start_at'),request('end_at')])
                              ->orWhereBetween('end_at',[request('start_at'),request('end_at')])
                              ->orWhere(function($qq){
                                  $qq->where('start_at','<=',request('start_at'))
                                    ->where('end_at','>=',request('end_at'));
                              });
                        })->exists();
                    if ($overlap) {
                        $fail('Produk ini sudah terjadwal/berjalan pada rentang waktu tersebut.');
                    }
                }
            ],
            'start_price' => ['required','numeric','min:0'],
            'increment'   => ['required','numeric','min:0.01'],
            'start_at'    => ['required','date','after_or_equal:today'],
            'end_at'      => [
                'required','date','after:start_at',
                function ($attr,$val,$fail) {
                    if (now()->greaterThan($val)) {
                        $fail('Waktu selesai harus setelah waktu saat ini.');
                    }
                },
            ],
        ];
    }
}
