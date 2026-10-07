<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\MediaItem;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function entry(Request $request)
    {
        return Auth::check() ? $this->index($request) : $this->loginForm();
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('admin.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.index');
    }

    public function index(Request $request)
    {
        $section = $request->query('section', 'dashboard');
        abort_unless(in_array($section, ['dashboard', 'gallery', 'categories', 'messages', 'settings'], true), 404);
        $items = MediaItem::with(['category', 'media'])->orderBy('position')->paginate(12)->withQueryString();
        $categories = Category::withCount('mediaItems')->orderBy('position')->get();
        $messages = ContactMessage::latest()->paginate(12, ['*'], 'messages_page')->withQueryString();
        return view('admin.index', [
            'section' => $section, 'items' => $items, 'categories' => $categories, 'messages' => $messages,
            'site' => SiteSetting::current(), 'stats' => [
                'items' => MediaItem::count(), 'published' => MediaItem::where('is_published', true)->count(),
                'categories' => Category::count(), 'messages' => ContactMessage::count(),
            ],
        ]);
    }

    public function saveItem(Request $request, ?MediaItem $item = null)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'type' => ['required', 'in:photo,video,embed'],
            'category_id' => ['nullable', 'exists:categories,id'], 'caption' => ['nullable', 'string', 'max:5000'],
            'alt_text' => ['nullable', 'string', 'max:255'], 'video_url' => ['nullable', 'url', 'max:2048'],
            'is_published' => ['nullable', 'boolean'], 'position' => ['nullable', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'video' => ['nullable', 'mimes:mp4,webm', 'max:32768'], 'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ]);
        $item ??= new MediaItem();
        $item->fill(collect($data)->except(['photo', 'video', 'poster'])->all());
        $item->is_published = $request->boolean('is_published');
        $item->save();
        foreach (['photo' => 'photo', 'video' => 'video', 'poster' => 'poster'] as $input => $collection) {
            if ($request->hasFile($input)) $item->addMediaFromRequest($input)->toMediaCollection($collection);
        }
        return redirect()->route('admin.index', ['section' => 'gallery'])->with('success', 'Le contenu a été enregistré.');
    }

    public function uploadFiles(Request $request)
    {
        $data = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm', 'max:32768'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ], [
            'files.required' => 'Sélectionne au moins un fichier à importer.',
            'files.*.mimes' => 'Seules les photos JPG, PNG, WebP et les vidéos MP4 ou WebM sont acceptées.',
            'files.*.max' => 'Chaque fichier doit faire moins de 32 Mo.',
        ]);

        foreach ($data['files'] as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $isPhoto = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
            $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $item = MediaItem::create([
                'title' => $title ?: 'Nouveau contenu',
                'type' => $isPhoto ? 'photo' : 'video',
                'category_id' => $data['category_id'] ?? null,
                'is_published' => true,
            ]);

            $item->addMedia($file)->toMediaCollection($isPhoto ? 'photo' : 'video');
        }

        return redirect()->route('admin.index', ['section' => 'gallery'])
            ->with('success', count($data['files']) . ' fichier(s) importé(s) et ajouté(s) à la galerie.');
    }

    public function deleteItem(MediaItem $item)
    {
        $item->delete();
        return back()->with('success', 'Le contenu a été supprimé.');
    }

    public function saveCategory(Request $request, ?Category $category = null)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:2000'], 'is_visible' => ['nullable', 'boolean']]);
        $category ??= new Category();
        $category->fill($data);
        $category->is_visible = $request->boolean('is_visible');
        $category->save();
        return redirect()->route('admin.index', ['section' => 'categories'])->with('success', 'La catégorie a été enregistrée.');
    }

    public function deleteCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'La catégorie a été supprimée.');
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:255'], 'tagline' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'], 'hero_subtitle' => ['nullable', 'string', 'max:5000'],
            'about_title' => ['nullable', 'string', 'max:255'], 'about_text' => ['nullable', 'string', 'max:10000'],
            'contact_email' => ['nullable', 'email', 'max:255'], 'city' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:500'], 'youtube' => ['nullable', 'url', 'max:500'],
            'vimeo' => ['nullable', 'url', 'max:500'], 'tiktok' => ['nullable', 'url', 'max:500'], 'linkedin' => ['nullable', 'url', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string', 'max:200'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ]);
        $site = SiteSetting::current();
        $avatar = $request->file('avatar');
        $site->fill(collect($data)->except('avatar')->all())->save();
        if ($avatar) $site->addMedia($avatar)->toMediaCollection('avatar');
        return back()->with('success', 'Les réglages ont été enregistrés.');
    }

    public function deleteMessage(ContactMessage $message)
    {
        $message->delete();
        return back()->with('success', 'Le message a été supprimé.');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'string', 'min:12', 'confirmed']]);
        $request->user()->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Le mot de passe a été changé.');
    }
}
