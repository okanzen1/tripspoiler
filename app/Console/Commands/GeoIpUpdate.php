<?php

namespace App\Console\Commands;

use App\Support\GeoIp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Ücretsiz DB-IP Lite şehir veritabanını indirir (ayda bir yenilenir; routes/console.php zamanlar).
 */
class GeoIpUpdate extends Command
{
    protected $signature = 'geoip:update';

    protected $description = 'IP → ülke/şehir veritabanını (DB-IP Lite) indirir';

    public function handle(): int
    {
        $target = storage_path(GeoIp::PATH);
        @mkdir(dirname($target), 0775, true);

        // Bu ayın dosyası henüz yayınlanmadıysa geçen ayınki
        foreach ([now(), now()->subMonthNoOverflow()] as $month) {
            $url = 'https://download.db-ip.com/free/dbip-city-lite-' . $month->format('Y-m') . '.mmdb.gz';
            $gz = $target . '.gz';
            $this->info("İndiriliyor: {$url}");
            $response = Http::timeout(600)->withOptions(['sink' => $gz])->get($url);
            if (!$response->successful() || filesize($gz) < 1_000_000) {
                @unlink($gz);
                continue;
            }

            $in = gzopen($gz, 'rb');
            $out = fopen($target . '.tmp', 'wb');
            while (!gzeof($in)) {
                fwrite($out, gzread($in, 1 << 20));
            }
            gzclose($in);
            fclose($out);
            @unlink($gz);
            rename($target . '.tmp', $target); // eski dosyanın yerine tek seferde geçer

            $this->info('Hazır: ' . round(filesize($target) / 1024 / 1024) . ' MB');

            return self::SUCCESS;
        }

        $this->error('Veritabanı indirilemedi.');

        return self::FAILURE;
    }
}
