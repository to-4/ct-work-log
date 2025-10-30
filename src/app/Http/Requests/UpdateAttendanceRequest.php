<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAttendanceRequest extends FormRequest
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

        $timePattern = '/^([01][0-9]|2[0-3]):[0-5][0-9]$/';
        $rules = [
            'clock_in_at'  => ['required', "regex:$timePattern"],
            'clock_out_at' => ['required', "regex:$timePattern"],
            'note'         => ['required', ],
        ];

        $breaks = $this->input('breaks', []);
        foreach($breaks as $key => $break) {

            if ($key === 'new') {
                $rules["breaks.$key.break_start_at"] = ['nullable', "regex:$timePattern"];
                $rules["breaks.$key.break_end_at"]   = ['nullable', "regex:$timePattern"];
            }
            else {
                $rules["breaks.$key.break_start_at"] = ['required', "regex:$timePattern"];
                $rules["breaks.$key.break_end_at"]   = ['required', "regex:$timePattern"];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'clock_in_at.required'  => '出勤時間を入力してください',
            'clock_in_at.regex'     => 'HH:MM の形式で入力してください',

            'clock_out_at.required' => '退勤時間を入力してください',
            'clock_out_at.regex'    => 'HH:MM の形式で入力してください',

            'note.required'         => '備考を記入してください',

            'breaks.*.break_start_at.required' => '休憩開始時間を入力してください',
            'breaks.*.break_start_at.regex'    => '休憩開始時間は HH:MM の形式で入力してください',

            'breaks.*.break_end_at.required'   => '休憩終了時間を入力してください',
            'breaks.*.break_end_at.regex'      => '休憩終了時間は HH:MM の形式で入力してください',
        ];
    }

    // バリデーション後の追加検証
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {

            $clockIn  = $this->input('clock_in_at');
            $clockOut = $this->input('clock_out_at');

            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('clock_in_at', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('breaks', []);
            foreach ($breaks as $key => $break) {
                $breakStart = $break['break_start_at'] ?? '';
                $breakEnd   = $break['break_end_at'] ?? '';

                if ($breakStart && $breakEnd) {
                    // 休憩開始時刻と休憩終了時刻の両方が存在
                    if ($breakEnd < $breakStart) { // 休憩終了 <  休憩開始
                        $validator->errors()->add("breaks.$key.break_start_at", '休憩時間が不適切な値です');
                    }
                    else if ($breakStart < $clockIn) { // 休憩開始 < 出勤
                        $validator->errors()->add("breaks.$key.break_start_at", '休憩時間が不適切な値です');
                    }

                    if ($clockOut < $breakEnd) { // 退勤 < 休憩終了
                        $validator->errors()->add("breaks.$key.break_end_at", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }

                if ($key === 'new') {

                    // 新規追加の休憩の場合は、どちらか一方のみ入力されている場合は、エラー
                    if (($breakStart === '' && $breakEnd !== '')
                        || ($breakStart !== '' && $breakEnd === '')) {
                            $validator->errors()->add("breaks.$key.break_start_at", '休憩時間の開始時刻または終了時刻のいずれか一方のみが入力されています');
                    }
                }
            }

        });
    }
}
