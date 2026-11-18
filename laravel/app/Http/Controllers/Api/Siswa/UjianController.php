<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class UjianController extends ApiController
{
    public function ujianIndex(Request $request) {}
    public function ujianPetunjuk(Request $request, $id) {}
    public function ujianPlay(Request $request, $id) {}
    public function ujianSubmit(Request $request, $id) {}
    public function ujianResult(Request $request, $id) {}
}
