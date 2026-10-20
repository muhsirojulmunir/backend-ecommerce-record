<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GameSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminWebGameController extends Controller
{
    /**
     * Tampilkan daftar dan konfigurasi game mini voucher.
     */
    public function index()
    {
        $games = GameSetting::all();

        // Ambil kategori untuk pilihan voucher khusus kategori
        $categoryQuery = Category::query();
        if (Schema::hasColumn('categories', 'is_active')) {
            $categoryQuery->where('is_active', true);
        }
        if (Schema::hasColumn('categories', 'sort_order')) {
            $categoryQuery->orderBy('sort_order');
        } else {
            $categoryQuery->orderBy('name');
        }
        $categories = $categoryQuery->get();

        return view('admin.games', compact('games', 'categories'));
    }

    /**
     * Aktif / Nonaktifkan game secara instan.
     */
    public function toggle(string $gameCode)
    {
        $game = GameSetting::where('game_code', $gameCode)->firstOrFail();
        $game->is_active = ! $game->is_active;
        $game->save();

        $status = $game->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Game {$game->name} berhasil {$status}.");
    }

    /**
     * Simpan pengaturan hadiah dan aturan game.
     */
    public function update(Request $request, string $gameCode)
    {
        $game = GameSetting::where('game_code', $gameCode)->firstOrFail();

        if ($gameCode === 'stopwatch') {
            $validated = $request->validate([
                'name'                  => 'required|string|max:100',
                'is_active'             => 'nullable|boolean',
                'daily_limit'           => 'required|integer|min:1|max:10',
                'category_id'           => 'nullable',
                'tiers.perfect.amount'  => 'required|integer|min:1000',
                'tiers.perfect.min'     => 'required|numeric|min:0|max:20',
                'tiers.perfect.max'     => 'required|numeric|min:0|max:20',
                'tiers.near.amount'     => 'required|integer|min:1000',
                'tiers.near.min'        => 'required|numeric|min:0|max:20',
                'tiers.near.max'        => 'required|numeric|min:0|max:20',
                'tiers.good.amount'     => 'required|integer|min:0',
            ], [
                'tiers.perfect.amount.required' => 'Nominal voucher Perfect wajib diisi.',
                'tiers.near.amount.required'    => 'Nominal voucher Near wajib diisi.',
                'tiers.good.amount.required'    => 'Nominal voucher Partisipasi wajib diisi.',
            ]);

            $config = $game->config ?? [];
            $config['daily_limit'] = (int) $validated['daily_limit'];
            $config['category_id'] = !empty($validated['category_id']) ? (int) $validated['category_id'] : null;

            // Tier Perfect
            $config['tiers']['perfect']['amount'] = (int) $validated['tiers']['perfect']['amount'];
            $config['tiers']['perfect']['min']    = (float) $validated['tiers']['perfect']['min'];
            $config['tiers']['perfect']['max']    = (float) $validated['tiers']['perfect']['max'];

            // Tier Near
            $config['tiers']['near']['amount']    = (int) $validated['tiers']['near']['amount'];
            $config['tiers']['near']['min']       = (float) $validated['tiers']['near']['min'];
            $config['tiers']['near']['max']       = (float) $validated['tiers']['near']['max'];

            // Tier Good
            $config['tiers']['good']['amount']    = (int) $validated['tiers']['good']['amount'];

            $game->name      = $validated['name'];
            $game->is_active = $request->boolean('is_active');
            $game->config    = $config;
            $game->save();

            return back()->with('success', "Pengaturan hadiah {$game->name} berhasil diperbarui.");
        }

        return back()->with('error', "Game dengan kode {$gameCode} belum memiliki handler konfigurasi.");
    }
}