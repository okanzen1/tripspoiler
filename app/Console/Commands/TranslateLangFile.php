<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateLangFile extends Command
{
    protected $signature = 'lang:file {name}';
    protected $description = 'Translate a lang file using Google Translate';

    public function handle()
    {
        $fileName = $this->argument('name');

        $sourceFile = lang_path("en/{$fileName}.php");

        if (!file_exists($sourceFile)) {
            $this->error("File not found: {$sourceFile}");
            return;
        }

        $languages = [
            'ar','de','es','fr','it','ja','ko','pt','ru','tr','zh'
        ];

        $source = require $sourceFile;

        foreach ($languages as $lang) {

            $this->info("Translating {$fileName} → {$lang}");

            $targetFile = lang_path("{$lang}/{$fileName}.php");

            if (!is_dir(lang_path($lang))) {
                mkdir(lang_path($lang), 0755, true);
            }

            $existing = file_exists($targetFile) ? require $targetFile : [];

            $translator = new GoogleTranslate();
            $translator->setSource('en');
            $translator->setTarget($lang);

            $translated = $this->translateRecursive($source, $existing, $translator, $lang);

            file_put_contents(
                $targetFile,
                "<?php\n\nreturn " . var_export($translated, true) . ";"
            );
        }

        $this->info("Translation completed for {$fileName}");
    }

    private function translateRecursive($source, $existing, $translator, $lang)
    {
        $result = [];

        foreach ($source as $key => $value) {

            if (is_array($value)) {

                $result[$key] = $this->translateRecursive(
                    $value,
                    $existing[$key] ?? [],
                    $translator,
                    $lang
                );

            } else {

                if (isset($existing[$key])) {

                    $this->line("Skipped {$lang} → {$key}");
                    $result[$key] = $existing[$key];

                } else {

                    try {

                        $translated = $translator->translate($value);

                        $this->line("{$lang} → {$key}");

                        $result[$key] = $translated;

                        usleep(200000);

                    } catch (\Exception $e) {

                        $this->error("Error translating {$lang} → {$key}");
                        $this->error($e->getMessage());

                        $result[$key] = $value;

                    }

                }

            }

        }

        return $result;
    }
}