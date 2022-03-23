<?php

namespace App\Fresns\Panel\Http\Requests;

class UpdateSessionKeyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'platform_id' => 'required|int',
            'name' => 'required|string',
            'type' => 'required|int',
            'is_enable' => 'required|boolean',
            'plugin_unikey' => 'exists:App\Models\Plugin,unikey',
        ];
    }

    public function attributes()
    {
        return [
            'platform_id' => __('FsLang::panel.platform'),
            'name' => __('FsLang::panel.name'),
            'type' => __('FsLang::panel.type'),
            'is_enable' => __('FsLang::panel.status'),
            'plugin_unikey' => __('FsLang::panel.associatePlugin'),
        ];
    }
}
