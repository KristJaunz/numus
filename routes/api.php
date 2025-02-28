<?php

use App\ProcessXML;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Route::post('upload/{file}', function (string $file) {

    // Get raw XML content from the request
    $xmlContent = request()->getContent();

    // Convert XML string to SimpleXMLElement
    try {
        $xmlObject = new SimpleXMLElement($xmlContent);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error','message' => 'Invalid XML format','code' => 0], 400);
    }

    $result = ProcessXML::run($xmlContent,$file);

    if (!$result) {
        return response()->json(['status' => 'error','message' => 'Failed to process data', 'code' => 1], 400);
    }


    return response()->json(['status' => 'success', 'message' => 'Data received successfully','code' => 2], 200);
});


