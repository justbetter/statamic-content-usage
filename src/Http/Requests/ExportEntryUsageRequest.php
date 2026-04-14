<?php

namespace JustBetter\StatamicContentUsage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $collection
 */
class ExportEntryUsageRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'collection' => 'required|string',
            'export_type' => 'required|string|in:used,unused',
        ];
    }
}
