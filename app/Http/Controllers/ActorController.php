<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActorRequest;
use App\Models\Actor;
use App\Services\ActorExtractionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActorController extends Controller
{
    public function __construct(
        private ActorExtractionService $extractionService
    ) {}

    /**
     * Display the actor creation form.
     */
    public function create()
    {
        return Inertia::render('Actors/Create');
    }

    /**
     * Store a new actor submission.
     */
    public function store(StoreActorRequest $request)
    {
        try {
            // Extract actor information using OpenAI
            $extractedData = $this->extractionService->extract($request->validated()['description']);

            // Validate that required fields are present
            if (!$this->extractionService->validateRequiredFields($extractedData)) {
                return back()
                    ->withErrors(['description' => 'Data not found'])
                    ->withInput();
            }

            // Create the actor record
            Actor::create([
                'email' => $request->validated()['email'],
                'description' => $request->validated()['description'],
                'first_name' => $extractedData['first_name'],
                'last_name' => $extractedData['last_name'],
                'address' => $extractedData['address'],
                'height' => $extractedData['height'],
                'weight' => $extractedData['weight'],
                'gender' => $extractedData['gender'],
                'age' => $extractedData['age'],
                'raw_ai_response' => $extractedData['raw_ai_response'],
            ]);

            return redirect()->route('actors.index')
                ->with('success', 'Actor information submitted successfully!');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['description' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display all actor submissions.
     */
    public function index()
    {
        $actors = Actor::select(['first_name', 'last_name', 'address', 'gender', 'height', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Actors/Index', [
            'actors' => $actors,
        ]);
    }
}
