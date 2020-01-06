<?php
namespace Modules\ModuleTpl\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Modules\ModuleTpl\Entities\ModelName;
use Modules\ModuleTpl\Listeners\DeleteItemRequest;
use Modules\ModuleTpl\Listeners\SaveFormRequest;

class ModelNameRequest extends FormRequest
{
    private $formValidated = [];
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
     * @param $form
     * @return array
     */
    public function rules($form)
    {
        $rules = [
            #TCCOLSRULES
        ];

        #TCREQUIREDFILE

        return $rules;
    }

#TCFILERULESFX

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

    public function validate(SaveFormRequest $event)
    {
        $request = request();

        $errors = [];

        $formErrors = [];

        $notRequiredErr = [];

        $model = new ModelName();

        foreach ($request->input('language') As $form => $formData) {

            if (!empty(request()->file('language.'.$form)))
                $formData = array_merge($formData, request()->file('language.'.$form));

            $validator = Validator::make(
                $formData,
                $this->rules($form),
                $this->messages()
            );

            if ($validator->fails()) {

                foreach ($validator->errors()->getMessages() As $key => $err) {
                    // Adding "label" on each item
                    $formErrors[$form][$key]['label'] = __('moduletpl::messages.'.$key.'_text');
                    // Form
                    $formErrors[$form][$key]['form'][] = 'moduletpl-lang-form-'.$formData[$model::LANG_FOREIGN_KEY];
                    foreach ($err As $ek => $er) {
                        $formErrors[$form][$key]['err_'.++$ek] = $er;

                        // Not required input error occurred indicator
                        if ($er == __('moduletpl::messages.input_required'))
                            $notRequiredErr[$form] = true;
                    }
                }

            } else
                $this->formValidated[$form] = $formData;
        }

        /**
         * Preparing errors response
         */
        if (!empty($formErrors)) {

            /**
             * Checking if there is value submitted from the
             * forms of languages
             */
            $formsHasData = false;

            $forms = $request->input('language');

            foreach ($forms As $key => $form)
                foreach ($form As $field => $val) {
                    if (in_array($field, $model->getFillable()))
                        if (!empty($val)) {
                            $formsHasData = true;
                            break;
                        }
                }

            /**
             * Finalizing the errors of forms
             * to response
             */
            foreach ($formErrors As $key => $err) {

                $skipErr = true;

                $form = $request->input('language.'.$key);
                if (!empty(request()->file('language.'.$key)))
                    $form = array_merge($form, request()->file('language.'.$key));

                foreach ($form As $field => $val) {
                    if (in_array($field, $model->getFillable())) {
                        if ((empty($val) && !empty($form[$model::MAIN_FOREIGN_KEY]) && isset($notRequiredErr[$key])) ||
                            (!empty($val) && empty($form[$model::MAIN_FOREIGN_KEY]) && isset($notRequiredErr[$key])) || !$formsHasData)
                            $skipErr = false;
                    }
                }

                if (!$skipErr) {
                    /**
                     * Merging errors to a single array
                     * for each column/input
                     */
                    foreach ($err As $col => $errInfo) {
                        if (isset($errors[$col]))
                            foreach ($errInfo As $lbl => $val) {
                                if (is_array($val))
                                    $errors[$col][$lbl] = array_merge($errors[$col][$lbl], $val);
                            }
                        else
                            $errors[$col] = $errInfo;
                    }
                }
            }

            // Arrange base on form input fields order
            $temp = [];
            foreach (config('moduletpl.form.languages') As $col => $val)
                if (isset($errors[$col]))
                    $temp[$col] = $errors[$col];

            $errors = $temp;
        }

        if (empty($errors))
            return true;
        else
            return $errors;

    }

    public function store(SaveFormRequest $event)
    {
        foreach ($this->formValidated As $form => $formData) {

            $model = new ModelName();
            $keyName = $model->getKeyName();

            if (!empty($formData[$keyName]))
                $model = ModelName::find($formData[$keyName]);

            $model->fill($formData);

            $fk = $model::MAIN_FOREIGN_KEY;
            $model->$fk = $event->event->id;

            $langFk = $model::LANG_FOREIGN_KEY;
            $model->$langFk = $form;

            $model->store($form);
        }
    }

    public function delete(DeleteItemRequest $event)
    {
        $model = ModelName::where(ModelName::MAIN_FOREIGN_KEY, $event->event->id);
        if (!is_null($model))
            $model->delete();
    }
}