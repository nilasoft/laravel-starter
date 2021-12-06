<?php

    namespace App\JsonApi\V1\Posts;

    use App\JsonApi\JsonapiShorthandTrait;
    use Illuminate\Validation\Rule;
    use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
    use LaravelJsonApi\Validation\Rule as JsonApiRule;

    class PostRequest extends ResourceRequest {
        use JsonapiShorthandTrait;

        /**
         * Get the validation rules for the resource.
         *
         * @return array
         */
        public function rules(): array {
            return [
                'title'   => [ $this->required(), 'string', 'min:3', 'max:1024' ],
                'content' => [ $this->required(), 'string', 'min:10', 'max:2048' ],
                'owner'   => [ JsonApiRule::toOne() ]
            ];
        }

    }
