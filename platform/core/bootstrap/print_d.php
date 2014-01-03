<?php

/*

Written by Pertti Soomann, 2014

Check for latest version and report issues at
https://github.com/vikerlane/print_d

Copyright (c) 2014, Pertti Soomann @ Vikerlane
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met: 

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer. 
2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution. 

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those
of the authors and should not be interpreted as representing official policies, 
either expressed or implied, of the FreeBSD Project.

*/

function print_d($var)
{
	$css = array(
		'holder' => 'border: 1px solid #eee; padding: 6px; background: #fff; float: left; margin: 3px; font-size: 11px; font-family:Lucida Console, Monaco, monospace;',
		'table' => 'border: 1px solid #ddd; border-collapse:collapse;',
		'table-methods' => 'margin-top: 4px; width: 100%;',
		'td' => 'border: 1px solid #ddd; font-size: 11px; vertical-align: top; padding: 2px 4px 2px 4px;',
		'method' => 'color: #00d;',
		'attributes' => 'padding: 2px;',
		'table-attributes' => 'width: 100%; border-collapse:collapse;',
		'required' => 'text-align: center; color: #d00; width: 16px;',
		'attribute' => 'width:120px;',
		'pre' => 'font-size: 11px !important; margin: 0; padding: 0; background: none; border: 0;',
		'type' => 'color: #bbb;',
		'type-array' => '',
		'type-object' => '',
		'type-' => '',
		'type-integer' => 'font-weight: bold; max-width: 250px; color: #00d; text-align: right;',
		'type-float' => 'font-weight: bold; max-width: 250px; color: #00d; text-align: right;',
		'type-double' => 'font-weight: bold; max-width: 250px; color: #00d; text-align: right;',
		'type-string' => 'font-weight: bold; max-width: 250px; color: #0d0;',
		'type-boolean' => 'font-weight: bold; max-width: 250px; color: #bbb;',
		'type-null' => 'font-weight: bold; max-width: 250px; color: #bbb;',
		'void' => 'font-style: italic; color: #bbb;',
		'emptystring' => 'color: #bbb; font-style: italic; font-weight: normal;'
	);

	$t = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	$s = file($t[0]['file']);
	$s = $s[$t[0]['line']-1];

	$t = explode('print_d(', $s, 2);
	if (count($t) > 1)
	{
		$t = trim(preg_replace('/\s+/', ' ', $t[1]));
		if (substr($t, 0, 1) == '$')
		{
			$t = explode(')', $t, 2);
			$name = $t[0];
			$name = trim(preg_replace('/\s+/', '', $name));
		}
	}

	$type = gettype($var);

	$ret = '<div style="'.$css['holder'].'">';

	if ($type === 'array' || $type === 'object')
	{
		if (isset($name))
			$ret .= '<strong>'.$name.'</strong>';

		if ($type === 'object')
			$ret .= ' '.get_class($var);
	}

	$ret .= '<table style="'.$css['table'].'">';
	switch($type)
	{
	 	case 'array':
	 	case 'object':
	 		foreach($var as $i => $v)
	 		{
	 			$v_type = strtolower(gettype($v));
	 			
	 			if ($v_type === 'object' || $v_type === 'array')
	 				$v = '<pre style="'.$css['pre'].'">'.print_r($v, true).'</pre>';
	 			else if ($v_type === 'boolean')
	 				$v = $v ? 'TRUE' : 'FALSE';
	 			else if ($v_type === 'string' && $v === '')
	 				$v = '<span style="'.$css['emptystring'].'">empty string</span>';
	 			else if ($v_type === 'null')
	 				$v = 'NULL';

	 			$ret .= '<tr>';
	 			$ret .= '<td style="'.$css['td'].$css['type'].'">'.$v_type.'</td>';
	 			$ret .= '<td style="'.$css['td'].'">'.$i.'</td>';
	 			$ret .= '<td style="'.$css['td'].$css['type-'.$v_type].'">'.$v.'</td>';
	 			$ret .= '</tr>';
	 		}

	 		if ($type === 'object')
	 		{
	 			$methods = get_class_methods($var);
	 			if ($methods)
	 			{
	 				$ret .= '</table>';
	 				$ret .= '<table style="'.$css['table'].$css['table-methods'].'">';

	 				foreach($methods as $m)
	 				{
	 					$r = new ReflectionMethod($var, $m);
	 					$params = $r->getParameters();

	 					$ret .= '<tr>';
	 					$ret .= '<td style="'.$css['td'].$css['method'].'">'.$m.'</td>';
	 					$ret .= '<td style="'.$css['td'].$css['attributes'].'">';
	 					if ($params)
	 					{
	 						$ret .= '<table style="'.$css['table-attributes'].'">';
							foreach ($params as $p)
							{
								if ($p->isDefaultValueAvailable())
								{
									$value = $p->getDefaultValue();
									$v_type = strtolower(gettype($value));

									if ($v_type === 'string' && $value === '')
										$value = '<span style="'.$css['emptystring'].'">empty string</span>';
									else if ($v_type === 'boolean')
										$value = $value ? 'TRUE' : 'FALSE';
									else if ($v_type === 'null')
										$value = 'NULL';
								}
								else
								{
									$value = '<span style="'.$css['emptystring'].'">n/a</span>';
									$v_type = '';
								}

								$ret .= '<tr>';
								$ret .= '<td style="'.$css['td'].$css['required'].'">'.($p->isOptional() ? '' : '*').'</td>';
								$ret .= '<td style="'.$css['td'].$css['attribute'].'">'.$p->getName().'</td>';
								$ret .= '<td style="'.$css['td'].$css['type-'.$v_type].'">'.$value.'</td>';
								$ret .= '</tr>';
							}
							$ret .= '</table>';
						}
						else
						{
							$ret .= '<span style="'.$css['void'].'">void</span>';
						}
	 					$ret .= '</td>';
	 					$ret .= '</tr>';
	 				}
	 			}
	 		}
 		break;
 		default:

 			if ($type === 'boolean')
	 			$var = $var ? 'TRUE' : 'FALSE';

 			$ret .= '<tr>';
 			$ret .= '<td style="'.$css['td'].$css['type'].'">'.$type.'</td>';
 			if (isset($name))
	 			$ret .= '<td style="'.$css['td'].'">'.$name.'</td>';
	 		$ret .= '<td style="'.$css['td'].$css['type-'.$type].'">'.$var.'</td>';
	 		$ret .= '</tr>';
 		break;
	}
	$ret .= '</table>';

	$ret .= '</div>';

	return $ret;
}