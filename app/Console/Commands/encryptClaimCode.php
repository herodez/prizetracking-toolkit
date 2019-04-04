<?php

namespace App\Console\Commands;

use App\Cryptography\DataEncryption;
use App\Redeemed;
use App\RedemptionAttribute;
use Illuminate\Console\Command;

class encryptClaimCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prize-toolkit:encrypt-claim-code';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt claim code in the table rewards.';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $encryptionKey = config('app.claim_code_encryption_key');
        
        if ($encryptionKey === null) {
            $this->warn("You don't have set an encryption key yet, please set it in your environment variables");
            return;
        }
        
        $this->encryptRedeemedClaimCode();
        $this->info("\n");
        $this->encryptRedemtionAttributesClaimCode();
    }
    
    /**
     * Claim code encryption on Redeemed table.
     * @return void
     */
    private function encryptRedeemedClaimCode()
    {
        $totalRedeems = Redeemed::where('claim_code', '!=', '')->where('decrypt_parameters', '')->count();
        $batchSize = 100;
        $bar = $this->output->createProgressBar($totalRedeems);
        
        $this->info('Starting claim codes encryption on ' . Redeemed::class);
        $bar->start();
        while ($totalRedeems > 0) {
            Redeemed::where('claim_code', '!=', '')->where('decrypt_parameters', '')->chunk($batchSize,
                static function ($redeems) use ($bar) {
                    foreach ($redeems as $redeem) {
                        $dataEncrypt = DataEncryption::encrypt($redeem->claim_code,
                            config('app.claim_code_encryption_key'));
                        $redeem->claim_code = $dataEncrypt['data'];
                        $redeem->decrypt_parameters = $dataEncrypt['decryptParameters'];
                        $redeem->save();
                        $bar->advance();
                    }
                    
                });
            $totalRedeems--;
        }
        $bar->finish();
        $this->info("\nClaim codes encryption finish on " . Redeemed::class);
    }
    
    /**
     * Claim code encryption on Redeemed table.
     * @return void
     */
    private function encryptRedemtionAttributesClaimCode()
    {
        $totalRedemption = RedemptionAttribute::where('name',
            RedemptionAttribute::NAME_CLAIM_CODE)->where('decrypt_parameters', '')->count();
        $batchSize = 100;
        $bar = $this->output->createProgressBar($totalRedemption);
        
        $this->info('Starting claim codes encryption on ' . RedemptionAttribute::class);
        $bar->start();
        while ($totalRedemption > 0) {
            RedemptionAttribute::where('name', RedemptionAttribute::NAME_CLAIM_CODE)->where('decrypt_parameters',
                '')->chunk($batchSize,
                static function ($redeems) use ($bar) {
                    foreach ($redeems as $redeem) {
                        $dataEncrypt = DataEncryption::encrypt($redeem->value,
                            config('app.claim_code_encryption_key'));
                        $redeem->value = $dataEncrypt['data'];
                        $redeem->decrypt_parameters = $dataEncrypt['decryptParameters'];
                        $redeem->save();
                        $bar->advance();
                    }
                    
                });
            $totalRedemption--;
        }
        $bar->finish();
        $this->info("\nClaim codes encryption finish on " . RedemptionAttribute::class);
    }
}
