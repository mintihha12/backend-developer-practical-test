<?php

namespace App\Http\Controllers;

use App\Exceptions\UserAlreadyLikedPostException;
use App\Exceptions\UserLikeOwnPostException;
use App\Http\Requests\PostToggleReactionRequest;
use App\Http\Resources\PostCollection;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function list()
    {
        // $posts = Post::withCount('likes')->with('tags')->paginate();

        $posts = Post::with('tags')
                        ->withCount('likes')
                        ->paginate();

        return new PostCollection($posts);
    }

    public function toggleReaction(PostToggleReactionRequest $request)
    {
        try {
            $post = Post::query()
                ->with([
                    'likes' => function (HasMany $query) {
                        $query->whereBelongsTo(Auth::user());
                    },
                ])
                ->findOrFail($request->validated('post_id'));

            // user tries to like his own post
            throw_if(Gate::denies('like-post', $post), UserLikeOwnPostException::class);

            // user already liked the post
            if ($post->likes->isNotEmpty()) {
                // reaction is like the post
                throw_if($request->boolean('like'), UserAlreadyLikedPostException::class);

                $post->likes->map->delete();

                return response()->json([
                    'status'  => Response::HTTP_OK,
                    'message' => 'You unlike this post successfully',
                ]);
            }

            $post->likes()->create([
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status'  => Response::HTTP_OK,
                'message' => 'You like this post successfully',
            ]);
        } catch (UserLikeOwnPostException $e) {
            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'You cannot like your post',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (UserAlreadyLikedPostException $e) {
            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'You already liked this post',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => Response::HTTP_NOT_FOUND,
                'message' => 'model not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function postReaction(PostToggleReactionRequest $request)
    {
        try {
            // Retrieve post with likes related to the current user
            $post = Post::with(['likes' => function (HasMany $query) {
                    $query->whereBelongsTo(Auth::user());
                }])
                ->findOrFail($request->post_id);

            // Check if user tries to like their own post
            if (Gate::denies('like-post', $post)) {
                throw new UserLikeOwnPostException();
            }

            // Check if user already liked the post
            if ($post->likes->isNotEmpty()) {
                if ($request->boolean('like')) {
                    throw new UserAlreadyLikedPostException();
                }

                // Unlike the post
                $post->likes->map->delete();

                return response()->json([
                    'status'  => Response::HTTP_OK,
                    'message' => 'You unliked this post successfully',
                ]);
            }

            // Like the post
            $post->likes()->create([
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status'  => Response::HTTP_OK,
                'message' => 'You liked this post successfully',
            ]);

        } catch (UserLikeOwnPostException $e) {
            return response()->json([
                'status'  => Response::HTTP_BAD_REQUEST,
                'message' => 'You cannot like your own post',
            ], Response::HTTP_BAD_REQUEST);
        } catch (UserAlreadyLikedPostException $e) {
            return response()->json([
                'status'  => Response::HTTP_BAD_REQUEST,
                'message' => 'You already liked this post',
            ], Response::HTTP_BAD_REQUEST);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => Response::HTTP_NOT_FOUND,
                'message' => 'Post not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
