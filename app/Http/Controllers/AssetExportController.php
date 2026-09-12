<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Assets\ExportAssetsCsvAction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AssetExportController extends Controller
{
    /**
     * Stream CSV export of assets matching query filters.
     */
    public function __invoke(Request $request, ExportAssetsCsvAction $exportAction): StreamedResponse
    {
        $search = $request->query('q') ? (string) $request->query('q') : null;
        $status = $request->query('status') ? (string) $request->query('status') : null;
        $type = $request->query('type') ? (string) $request->query('type') : null;

        return $exportAction->execute($search, $status, $type);
    }
}
