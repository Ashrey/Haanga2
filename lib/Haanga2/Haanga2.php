<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2012 César Rodas and Meneame SL                                   |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/
namespace Haanga2;

use Haanga2\Compiler\Tokenizer,
    Haanga2_Compiler_Parser as Parser;

class Haanga2
{
    protected $loader;
    protected $Extension;
    protected $Tokenizer;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
        $this->Tokenizer = new Tokenizer;
        $this->Extension = new Extension($this->Tokenizer);
    }

    public function compile($source, $context = array())
    {
        $tokens = $this->Tokenizer->tokenize($source);
        $parser = new Parser;
        foreach ($tokens as $token) {
            $parser->doParse($token[0], $token[1]);
        }
        $parser->doParse(0, 0);
        $tree = $parser->body;
        $vm   = new Compiler\Dumper();
        $vm->setContext($context);
        $vm->writeLn('function ($context, $return)')
            ->writeLn('{')
            ->indent()
                ->evaluate($tree)
            ->dedent()
            ->writeLn('}');
        die($vm->buffer);
    }

    public function load($tpl, $vars = array(), $return = false)
    {
        $callback = $this->loader->load($tpl);
        if ($callback && is_callable($callback)) {
            return $callback($tpl, $vars, $return);
        }

        /* compile, compile, compile! */
        $this->compile($this->loader->getContent($tpl), $vars);
    }
}
