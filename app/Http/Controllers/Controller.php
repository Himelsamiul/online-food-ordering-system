<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * A safe page size from a user-supplied ?per_page.
     *
     * Taking the raw value straight into paginate() is a live bug: `?per_page=0`
     * or any non-numeric value casts to 0 and LengthAwarePaginator then divides
     * by it (500), while `?per_page=1000000` lets anyone pull an entire table in
     * one request. Only the sizes the UI actually offers are honoured.
     *
     * @param  list<int>  $allowed
     */
    protected function perPage(Request $request, int $default = 25, array $allowed = [10, 15, 20, 25, 50, 100]): int
    {
        $requested = (int) $request->query('per_page', $default);

        return in_array($requested, $allowed, true) ? $requested : $default;
    }
}
