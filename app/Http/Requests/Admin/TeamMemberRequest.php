<?php

namespace App\Http\Requests\Admin;

use App\Models\TeamMember;
use App\Support\Media\MediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class TeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('member');

        return $member ? $this->user()->can('update', $member) : $this->user()->can('create', TeamMember::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_photo' => $this->boolean('remove_photo'),
            'photo_media_id' => $this->input('photo_media_id') ?: null,
            'linkedin_url' => trim((string) $this->input('linkedin_url')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'team_group_id' => ['required', 'integer', Rule::exists('team_groups', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'url:http,https', 'max:500'],
            'photo' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(MediaService::MAX_KILOBYTES), 'mimetypes:image/jpeg,image/png,image/webp'],
            'photo_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'remove_photo' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    /** Column values only; the uploaded file and the remove flag are handled by the controller. */
    public function memberData(): array
    {
        $data = $this->safe()->except(['photo', 'remove_photo']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($this->boolean('remove_photo')) {
            $data['photo_media_id'] = null;
        }

        return $data;
    }
}
