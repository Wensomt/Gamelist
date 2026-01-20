<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Główny pulpit admina (admin.blade.php)
     */
    public function index()
    {
        return view('admin.admin');
    }

    /**
     * Lista wszystkich użytkowników (admin-users.blade.php)
     */
    public function listUsers(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'is_admin', 'created_at')->get();

        // Jeśli zapytanie chce JSON (np. Twoje stare API), dajemy JSON
        if ($request->expectsJson()) {
            return response()->json(['users' => $users], 200);
        }

        // Domyślnie zwracamy Twój nowy widok Blade
        return view('admin.admin-users', compact('users'));
    }

    /**
     * Przełączanie roli admina (Toggle) - DODANO ZABEZPIECZENIE
     */
    public function toggleAdmin($id)
    {
        $user = User::findOrFail($id);

        // Nie możesz zmienić uprawnień samemu sobie
        if ($id == Auth::id()) {
            // SPRAWDZAMY CZY TO OSTATNI ADMIN
            $adminCount = User::where('is_admin', true)->count();
            
            if ($adminCount <= 1 && $user->is_admin) {
                return back()->with('error', 'Nie możesz odebrać sobie uprawnień admina, ponieważ jesteś ostatnim administratorem w systemie!');
            }
            
            return back()->with('error', 'Nie możesz zmienić uprawnień samemu sobie.');
        }

        // SPRAWDZAMY CZY UŻYTKOWNIK DO ZMIANY JEST OSTATNIM ADMINEM
        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            
            if ($adminCount <= 1) {
                return back()->with('error', 'Nie można odebrać uprawnień ostatniemu administratorowi w systemie!');
            }
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $action = $user->is_admin ? 'nadano' : 'odebrano';
        return back()->with('success', "Uprawnienia administratora zostały {$action} użytkownikowi {$user->name}.");
    }

    /**
     * Usuwanie użytkownika - DODANO ZABEZPIECZENIE
     */
    public function deleteUser(Request $request, $id)
    {
        // Nie możesz usunąć własnego konta
        if ($id == Auth::id()) {
            $msg = 'Nie możesz usunąć własnego konta.';
            return $request->expectsJson() 
                ? response()->json(['message' => $msg], 403) 
                : back()->with('error', $msg);
        }

        $user = User::findOrFail($id);
        
        // SPRAWDZAMY CZY UŻYTKOWNIK DO USUNIĘCIA JEST OSTATNIM ADMINEM
        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            
            if ($adminCount <= 1) {
                $msg = 'Nie można usunąć ostatniego administratora w systemie!';
                return $request->expectsJson() 
                    ? response()->json(['message' => $msg], 403) 
                    : back()->with('error', $msg);
            }
        }

        $userName = $user->name;
        $user->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Użytkownik usunięty.'], 200);
        }

        return redirect()->route('admin.users')->with('success', "Użytkownik {$userName} został usunięty.");
    }
}