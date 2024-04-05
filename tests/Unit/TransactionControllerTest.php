<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\TransactionController;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Mockery;

class TransactionControllerTest extends TestCase
{
    public function testCommitMethodHandlesNetworkFailure()
    {
       // Mock the Transaction model
        $transaction = Mockery::mock(Transaction::class);
        $transaction->shouldReceive('findOrFail')->with(1)->andReturnSelf()->once();
        $transaction->shouldReceive('setAttribute')->with('status', 'committed')->once();
        $transaction->shouldReceive('save')->once()->andThrow(new \Exception('Network failure during commit'));


        // Create a new instance of the TransactionController
        $controller = new TransactionController();

        // Execute the commit method and capture the response
        $request = new Request();
        $response = $controller->commit($request, 1);

        // Assert that the response is a JSON response with a 500 status code
        $response->assertStatus(500); // Expecting Internal Server Error status code
    }

    public function testConcurrentTransactions()
    {
    
        // Mock the Transaction model
        $transaction = Mockery::mock(Transaction::class);
        // Expect the findOrFail method to be called twice with different transaction IDs
        $transaction->shouldReceive('findOrFail')->with(1)->andReturn(new Transaction(['status' => 'pending']))->once();
        $transaction->shouldReceive('findOrFail')->with(2)->andReturn(new Transaction(['status' => 'pending']))->once();
        
        // Create a new instance of the TransactionController
        $controller = new TransactionController();

        // Execute the start method for two transactions concurrently and capture the response
        $request1 = new Request();
        $response1 = $controller->start($request1);
        
        $request2 = new Request();
        $response2 = $controller->start($request2);

        // Extract the transaction IDs from the responses
        $transactionId1 = $response1->getContent();
        $transactionId2 = $response2->getContent();

        // Execute the commit method for the first transaction
        $response1 = $controller->commit(new Request(), $transactionId1);

        // Execute the commit method for the second transaction
        $response2 = $controller->commit(new Request(), $transactionId2);

        // Assert that the response for the first transaction is successful (status code 200)
        $this->assertEquals(200, $response1->getStatusCode());

        // Assert that the response for the second transaction is a conflict (status code 409)
        $this->assertEquals(409, $response2->getStatusCode());
    }
}
