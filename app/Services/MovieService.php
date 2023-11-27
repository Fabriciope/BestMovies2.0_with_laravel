<?php

namespace App\Services;

use App\DTOs\MovieDTO;
use App\Http\Requests\StoreUpdateMovieRequest;
use App\Models\Movie;
use App\Repositories\MovieRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MovieService
{
    public function __construct(
        private MovieRepository $repository
    ) {
    }

    public function create(StoreUpdateMovieRequest $request): Movie|bool
    {
        $movieDTO = MovieDTO::makeFromRequest($request);
        $movieDTO->user_id = Auth::user()->id;
        
        if ($movieDTO->duration == '0:0') {
            return false;
        }

        $movieDTO->poster = Storage::disk('public')->put('posters', $request->file('poster'));

        $createdMovie = $this->repository->store($movieDTO);
        if (!$createdMovie instanceof Movie) {
            return false;
        }

        return $createdMovie;
    }
    
    public function delete(string|int $id)
    {   
        $this->repository->delete(intval($id));
    }
}
