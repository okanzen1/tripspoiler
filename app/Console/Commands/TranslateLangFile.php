<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DeepL\Translator;

class TranslateLangFile extends Command
{
    protected $signature = 'lang:file {name}';
    protected $description = 'Translate a specific lang file using DeepL';

    public function handle()
    {
        $fileName = $this->argument('name');

        $sourceFile = lang_path("en/{$fileName}.php");

        if (!file_exists($sourceFile)) {
            $this->error("File not found: {$sourceFile}");
            return;
        }

        $translator = new Translator(env('DEEPL_API_KEY'));

        $languages = [
            'ar','de','es','fr','it','ja','ko','pt','ru','tr','zh'
        ];

        $deeplMap = [
            'ar' => 'AR',
            'de' => 'DE',
            'es' => 'ES',
            'fr' => 'FR',
            'it' => 'IT',
            'ja' => 'JA',
            'ko' => 'KO',
            'pt' => 'PT-PT',
            'ru' => 'RU',
            'tr' => 'TR',
            'zh' => 'ZH'
        ];

        $en = require $sourceFile;

        foreach ($languages as $lang) {

            $this->info("Translating {$fileName} → {$lang}");

            $targetFile = lang_path("{$lang}/{$fileName}.php");

            // klasör yoksa oluştur
            if (!is_dir(lang_path($lang))) {
                mkdir(lang_path($lang), 0755, true);
            }

            // mevcut çeviri varsa yükle
            if (file_exists($targetFile)) {
                $translated = require $targetFile;
            } else {
                $translated = [];
            }

            foreach ($en as $key => $text) {

                // zaten çevrilmişse atla
                if (isset($translated[$key])) {
                    $this->line("Skipped {$lang} → {$key}");
                    continue;
                }

                try {

                    $result = $translator->translateText(
                        $text,
                        'EN',
                        $deeplMap[$lang]
                    );

                    $translated[$key] = $result->text;

                    $this->line("{$lang} → {$key}");

                    // DeepL rate limit koruması
                    usleep(200000);

                } catch (\Exception $e) {

                    $this->error("Error translating {$lang} → {$key}");
                    $this->error($e->getMessage());

                    // hata olursa dur ve kaydet
                    break;
                }
            }

            file_put_contents(
                $targetFile,
                "<?php\n\nreturn " . var_export($translated, true) . ";"
            );
        }

        $this->info("Translation completed for {$fileName}");
    }
}