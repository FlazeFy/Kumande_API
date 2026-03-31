<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     required={"id", "consume_id", "payment_method", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="consume_id", type="string", description="Consume ID"),
 *     @OA\Property(property="payment_method", type="string", description="Method of the payment"),
 *     @OA\Property(property="payment_price", type="integer", description="Ammount of the consume price in Rupiah"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the payment was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the payment was updated"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the payment"),
 * )
 */

class Payment extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $table = 'payment';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'consume_id', 'payment_method', 'payment_price', 'created_at', 'updated_at', 'created_by'];

    public static function getLifeTimeSpend($user_id) {
        return Payment::where('created_by', $user_id)
            ->selectRaw('
                COUNT(DISTINCT DATE(created_at)) as total_days,
                CAST(COALESCE(SUM(payment_price), 0) as UNSIGNED) as total_payment
            ')
            ->first();
    }

    public static function findAllMonthlyPayment($user_id, $year) {
        $pym = Payment::where('created_by', $user_id)
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, SUM(payment_price) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))->map(function ($m) use ($pym) {
            return [
                'context' => date('M', mktime(0, 0, 0, $m, 1)),
                'total' => (int) ($pym[$m] ?? 0),
            ];
        });
    }

    public static function findAllDailyPayment($user_id, $year, $month) {
        $pym = Payment::where('created_by', $user_id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('DAY(created_at) as day, SUM(payment_price) as total')
            ->groupBy('day')
            ->pluck('total', 'day'); 

        $maxDay = date("t", strtotime("$year-$month-01"));

        return collect(range(1, $maxDay))->map(function ($d) use ($pym) {
            return [
                'context' => (string) $d,
                'total' => (int) ($pym[$d] ?? 0),
            ];
        });
    }

    public static function getMonthlyPaymentStats($user_id, $year, $month) {
        $daily = Payment::where('created_by', $user_id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('SUM(payment_price) as total')
            ->groupByRaw('DAY(created_at)');

        return Payment::fromSub($daily, 'q')
            ->selectRaw('
                CAST(IFNULL(ROUND(AVG(total)), 0) as UNSIGNED) as average,
                CAST(IFNULL(MAX(total), 0) as UNSIGNED) as max,
                CAST(IFNULL(MIN(total), 0) as UNSIGNED) as min,
                CAST(IFNULL(SUM(total), 0) as UNSIGNED) as total
            ')
            ->first();
    }

    public static function getAllMonthlySpend($user_id, $year, $month, $limit = null) {
        $res = Payment::select('consume_name','consume_type','consume_id','payment_method','payment_price','payment.created_at')
            ->join('consume','consume.id','=','payment.consume_id')
            ->where('payment.created_by',$user_id)
            ->whereRaw("DATE_FORMAT(payment.created_at, '%b') = ?",[$month])
            ->whereRaw('YEAR(payment.created_at) = ?',[$year])
            ->orderby('payment.created_by','DESC');

        return $limit ? $res->paginate($limit) : $res->get();
    }
    
    public static function createPayment($data, $user_id) {
        $data['updated_at'] = null;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return Payment::create($data);
    }

    public static function updatePaymentById($data, $user_id, $id) {
        $data['updated_at'] = date('Y-m-d H:i:s');

        return Payment::where('created_by', $user_id)->where('id', $id)->update($data);
    }

    public static function deletePaymentById($user_id, $id) {
        return Payment::where('created_by', $user_id)->where('id', $id)->delete();
    }
}
