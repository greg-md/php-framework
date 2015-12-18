<?php

namespace Greg\Regex;

use Greg\Tool\Obj;

class InNamespace
{
    protected $start = null;

    protected $end = null;

    protected $recursive = true;

    protected $capture = true;

    protected $capturedKey = null;

    protected $allowEmpty = true;

    protected $match = null;

    protected $escape = null;

    protected $disableIn = [];

    protected $newLines = false;

    protected $trim = false;

    public function __construct($start, $end = null, $recursive = null)
    {
        $this->start($start);

        if ($end === null) {
            $end = $start;
        }

        $this->end($end);

        if ($recursive !== null) {
            $this->recursive($recursive);
        }

        return $this;
    }

    public function disableInQuotes()
    {
        $this->disableIn("'");

        $this->disableIn('"');

        return $this;
    }

    public function disableIn($start = null, $end = null)
    {
        if (func_num_args()) {
            $this->disableIn[] = [$start, $end ?: $start];

            return $this;
        }

        return $this->disableIn;
    }

    public function replaceCallback(callable $callable, $string, $flags = null)
    {
        return preg_replace_callback('#' . $this->toString() . '#' . $flags, $callable, $string);
    }

    public function toString()
    {
        $captureS = $captureE = null;

        if ($this->capture()) {
            $captureS = '(';

            if ($capturedKey = $this->capturedKey()) {
                $captureS .= "?'{$capturedKey}'";
            }

            $captureE = ')';
        }

        $start = preg_quote($this->start());

        $end = preg_quote($this->end());

        $allows = [];

        if ($this->disableIn) {
            foreach($this->disableIn as $capture) {
                $allows[] = preg_quote($capture[0]) . '.*?' . preg_quote($capture[1]);
            }
        }

        if ($escape = $this->escape()) {
            // Allow escaped start
            $allows[] = "{$escape}{$start}";

            // Allow escaped end
            $allows[] = "{$escape}{$end}";
        }

        // Allow all instead of start and end
        $allows[] = "(?!{$start})(?!{$end}).";

        if ($this->newLines()) {
            $allows[] = '\r?\n';
        }

        $matches = [
            $this->match() ?: '(?:' . implode('|', $allows) . ')',
        ];

        if ($this->recursive()) {
            if ($recursiveGroup = $this->recursiveGroup()) {
                $matches[] = '\g\'' . $recursiveGroup . '\'';
            } else {
                $matches[] = '(?R)';
            }
        }

        $matches = implode('|', $matches);

        $flag = ($this->allowEmpty() ? '*' : '+') . '?';

        $trim = $this->trim() ? '\s*' : '';

        $noEscaped = null;

        if ($escape) {
            $escape = preg_quote($escape);

            $noEscaped = "(?<!{$escape})";
        }

        return "{$noEscaped}{$start}{$trim}{$captureS}(?>{$matches}){$flag}{$captureE}{$trim}{$noEscaped}{$end}";
    }

    public function __toString()
    {
        return (string)$this->toString();
    }

    public function start($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function end($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function recursive($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function recursiveGroup($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function capture($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function capturedKey($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function allowEmpty($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function match($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function escape($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function newLines($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function trim($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}