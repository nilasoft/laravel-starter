<?php

    namespace Nila\Payments\Models\Requests;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Nila\Payments\Models\Wallet;

    class BankAccount extends Model {
        use HasFactory;

        protected $fillable = [
            'address',
            'holder'
        ];

        public function wallet() {
            return $this->belongsTo( Wallet::class );
        }

    }
