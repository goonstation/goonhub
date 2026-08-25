<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permissions\NumbersStationPermissions;
use App\Helpers\HasAnyAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\NumbersStationPasswordResource;
use App\Models\NumbersStationPassword;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

#[Group('Numbers Station')]
class NumbersStationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasAnyAbility::using(NumbersStationPermissions::VIEW, only: ['index']),
        ];
    }

    /**
     * Get
     *
     * Get the current numbers representing the password for the numbers station terminal
     */
    public function index(Request $request)
    {
        $numbersPass = NumbersStationPassword::firstOrFail();

        return new NumbersStationPasswordResource($numbersPass);
    }
}
