<?php
namespace Modules\ModuleTpl\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Lang;
use Modules\ModuleTpl\Entities\ModelName;

class ModelNameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            #TCCOLSRULES
        ];

#TCREQUIREDFILE

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            #TCCOLSMGS
        ];
    }

    /**
     * Modifying error message before sending back to Front
     *
     * @param Validator $validator
     */
    public function failedValidation(Validator $validator)
    {
        $errors = [];

        /**
         * Modifying errors before sending back to the from
         * This is for front side the will handle the errors
         * of the js helper
         */
        foreach ($validator->errors()->getMessages() As $key => $err){
            // Adding "label" on each item
            $errors[$key]['label'] = Lang::get('moduletpl::messages.'.$key.'_text');
            foreach ($err As $ek => $er)
                $errors[$key]['err_'.++$ek] = $er;
        }

        // Save action to logs
        $itemId = request()->route('id') ?? null;
        $logType = (!$itemId) ? ModelName::ADD : ModelName::UPDATE;

        $title = Lang::get('moduletpl::messages.save_item');
        $message = Lang::get('moduletpl::messages.'.strtolower($logType) . '_failed');

        // Album Model
        $calendar = new ModelName();
        $calendar->logAction(false, $title, $message, $logType, $itemId);

        $jsonResponse = [
            'success' => 0, // Flag trigger on front side that error occurred
            'errors' => $errors,
            'title' => $title,
            'message' => $message,
        ];

        throw new HttpResponseException(response()->json($jsonResponse, 200));
    }
}