<?php


    namespace App\JsonApi;


    use Illuminate\Validation\Rule;
    use Illuminate\Validation\Rules\Unique;

    trait JsonapiShorthandTrait {
        /**
         * fields required when creating
         *
         * @return string|null
         */
        private function required(): ?string {
            return $this->isCreating() ? 'required' : null;
        }

        /**
         * apply the unique rule
         *
         * @return Unique
         */
        private function unique(): Unique {
            $unique = Rule::unique( 'users', 'email' );
            $this->model() ? $unique->ignore( $this->model() ) : 'Do nothing';

            return $unique;
        }
    }
