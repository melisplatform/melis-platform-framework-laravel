<?php
namespace Modules\ModuleTpl\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Modules\ModuleTpl\Entities\ModelName;
use Modules\ModuleTpl\Listeners\DeleteItemRequest;
use Modules\ModuleTpl\Listeners\SaveFormRequest;

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

    public function validate(SaveFormRequest $request)
    {
        $validator = Validator::make(
            request()->input(),
            $this->rules(),
            $this->messages()
        );

        if (!$validator->fails()) {

            return true;

        } else {

            $errors = [];

            foreach ($validator->errors()->getMessages() As $key => $err){
                // Adding "label" on each item
                $errors[$key]['label'] = __('moduletpl::messages.'.$key.'_text');
                foreach ($err As $ek => $er)
                    $errors[$key]['err_'.++$ek] = $er;
            }

            return $errors;
        }
    }

    public function store(SaveFormRequest $request)
    {
        $model = new ModelName();
        if ($request->event->id)
            $model = ModelName::find($request->event->id);

        // Fill with Data from input
        $model->fill(request()->input());

        // Save
        $model->store();

        // Current item id
        $keyName = $model->getKeyName();
        $request->event->id = $model->$keyName;
    }

    public function delete(DeleteItemRequest $event)
    {
        $model = ModelName::find($event->event->id);
        if (!is_null($model))
            $model->delete();
    }
}