<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rich content moved from plain text to sanitised HTML written in the
     * admin editor (docs/CMS.md §3). Convert the stored plain text with the
     * rules it was rendered by until now: `# …` lines are headings, `-`,
     * `*` or `•` lines are bullets, blank lines separate paragraphs and a
     * single line break is kept. Rows that already hold HTML are skipped.
     */
    public function up(): void
    {
        $this->convert('articles', 'content', 'h2');
        $this->convert('job_openings', 'body', 'h3');
        $this->convert('businesses', 'content');
        $this->convert('pages', 'content');
        $this->convert('faqs', 'answer');
    }

    /** Content now; rolling back leaves it in place (plain text still renders). */
    public function down(): void {}

    /** @param  string|null  $heading  tag for `# …` lines; null = paragraphs only */
    protected function convert(string $table, string $column, ?string $heading = null): void
    {
        DB::table($table)->whereNotNull($column)->where($column, '!=', '')->orderBy('id')
            ->each(function (object $row) use ($table, $column, $heading) {
                $text = (string) $row->{$column};

                if (preg_match('/<\/?[a-z][a-z0-9]*(\s[^>]*)?\/?>/i', $text)) {
                    return;
                }

                DB::table($table)->where('id', $row->id)->update([$column => $this->toHtml($text, $heading)]);
            });
    }

    protected function toHtml(string $text, ?string $heading): string
    {
        $html = [];
        $paragraph = [];
        $list = [];

        $flush = function () use (&$html, &$paragraph, &$list) {
            if ($paragraph !== []) {
                $html[] = '<p>'.implode('<br>', array_map('e', $paragraph)).'</p>';
            }
            if ($list !== []) {
                $html[] = '<ul>'.implode('', array_map(fn (string $i) => '<li><p>'.e($i).'</p></li>', $list)).'</ul>';
            }
            $paragraph = $list = [];
        };

        foreach (preg_split('/\R/u', trim($text)) as $line) {
            $line = trim($line);

            if ($line === '') {
                $flush();
            } elseif ($heading && preg_match('/^#+\s*(.+)$/u', $line, $m)) {
                $flush();
                $html[] = "<{$heading}>".e(trim($m[1]))."</{$heading}>";
            } elseif ($heading && preg_match('/^[-*•]\s*(.+)$/u', $line, $m)) {
                if ($paragraph !== []) {
                    $flush();
                }
                $list[] = trim($m[1]);
            } else {
                if ($list !== []) {
                    $flush();
                }
                $paragraph[] = $line;
            }
        }

        $flush();

        return implode("\n", $html);
    }
};
