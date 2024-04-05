<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function start(Request $request)
    {
        // Create a new transaction record
        $transaction = Transaction::create(['status' => 'pending']);

        return response()->json(['transaction_id' => $transaction->id], 201);
    }

    public function commit(Request $request, $transactionId)
{
    // Find the transaction
    $transaction = Transaction::findOrFail($transactionId);

    // Check if the transaction is already committed
    if ($transaction->status === 'committed') {
        return response()->json(['error' => 'Transaction already committed'], 409); // Conflict status code
    }

    // Attempt to update the transaction status to committed
    $transaction->status = 'committed';

    // Check for errors during update
    try {
        $transaction->saveOrFail();
    } catch (\Exception $e) {
        // Handle network failure during commit
        return response()->json(['error' => 'Network failure during commit'], 500); // Internal Server Error status code
    }

    // If no exception is thrown, return success response
    return response()->json(['message' => 'Transaction committed successfully'], 200);
}

    public function rollback(Request $request, $transactionId)
    {
        // Find the transaction
        $transaction = Transaction::findOrFail($transactionId);

        // Update the transaction status to rolled back
        $transaction->update(['status' => 'rolledback']);

        return response()->json(['message' => 'Transaction rolled back successfully']);
    }
}
