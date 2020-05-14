<?php
declare(strict_types=1);

/*
    Copyright (C) <2020>  <Andy Daniel Navarro Taño>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace ThenLabs\PyramidalTests\Model;

use Closure;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class Model
{
    const FUNCTION_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    protected $description;
    protected $closure;
    protected $comments = [];

    public function __construct(string $description, ?Closure $closure = null)
    {
        $this->description = $description;
        $this->closure = $closure;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getClosure(): Closure
    {
        return $this->closure;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    public function getDocComments(): string
    {
        $result = '';

        foreach ($this->comments as $comment) {
            $result .= " * {$comment}\n";
        }

        return $result;
    }

    abstract public function getName(): string;
}
