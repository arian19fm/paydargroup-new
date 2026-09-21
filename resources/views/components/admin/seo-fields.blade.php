{{--
    Reusable SEO section for content forms. Fields post as seo[...] and are
    validated by ValidatesSeoFields; blank = fall back to the content.
    Character counts are advisory (data-char-count), never hard limits.
--}}
@props(['seo' => null])
@php $seo ??= new \App\Models\SeoMeta; @endphp
<fieldset class="card mb-4">
    <legend class="card-header h6 mb-0 py-2">{{ __('admin.seo.section') }}</legend>
    <div class="card-body">
        <p class="form-text mt-0">{{ __('admin.seo.help') }}</p>
        <div class="row">
            <div class="col-md-6">
                <x-admin.form.input name="seo[title]" :label="__('admin.seo.title')" :value="$seo->title" :help="__('admin.seo.title_help')" :counter="60" />
            </div>
            <div class="col-md-6">
                <x-admin.form.input name="seo[canonical_url]" type="url" :label="__('admin.seo.canonical_url')" :value="$seo->canonical_url" :help="__('admin.seo.canonical_url_help')" />
            </div>
        </div>
        <x-admin.form.textarea name="seo[description]" :label="__('admin.seo.description')" :value="$seo->description" :help="__('admin.seo.description_help')" :counter="160" rows="2" />
        <div class="d-flex gap-4">
            <x-admin.form.checkbox name="seo[robots_index]" :label="__('admin.seo.robots_index')" :checked="$seo->exists ? $seo->robots_index : true" />
            <x-admin.form.checkbox name="seo[robots_follow]" :label="__('admin.seo.robots_follow')" :checked="$seo->exists ? $seo->robots_follow : true" />
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-admin.form.input name="seo[og_title]" :label="__('admin.seo.og_title')" :value="$seo->og_title" :counter="60" />
                <x-admin.form.textarea name="seo[og_description]" :label="__('admin.seo.og_description')" :value="$seo->og_description" :counter="200" rows="2" />
                <x-admin.form.input name="seo[og_image_id]" type="number" min="1" :label="__('admin.seo.og_image_id')" :value="$seo->og_image_id" />
            </div>
            <div class="col-md-6">
                <x-admin.form.input name="seo[twitter_title]" :label="__('admin.seo.twitter_title')" :value="$seo->twitter_title" :counter="60" />
                <x-admin.form.textarea name="seo[twitter_description]" :label="__('admin.seo.twitter_description')" :value="$seo->twitter_description" :counter="200" rows="2" />
                <x-admin.form.input name="seo[twitter_image_id]" type="number" min="1" :label="__('admin.seo.twitter_image_id')" :value="$seo->twitter_image_id" />
            </div>
        </div>
        <x-admin.form.select name="seo[schema_type]" :label="__('admin.seo.schema_type')" :options="array_combine(config('cms.schema_types'), config('cms.schema_types'))" :selected="$seo->schema_type" :placeholder="__('admin.none')" />
    </div>
</fieldset>
