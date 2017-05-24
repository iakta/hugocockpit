<?php
/*
 * @Copyright (c) 2013 Leonel Quinteros
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 * * Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following disclaimer
 *   in the documentation and/or other materials provided with the
 *   distribution.
 * * Neither the name of the  nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

/**
 * PHP parser for TOML language: https://github.com/mojombo/toml
 *
 * @author Leonel Quinteros https://github.com/leonelquinteros
 *
 * @version 0.4.1
 *
 */
class Toml
{
    /**
     * Reads string from specified file path and parses it as TOML.
     *
     * @param (string) File path
     *
     * @return (array) Toml::parse() result
     */
    public static function parseFile($path)
    {
        if(!is_file($path))
        {
            throw new Exception('Invalid file path');
        }

        $toml = file_get_contents($path);

        // Remove BOM if present
        $toml = preg_replace('/^' . pack('H*','EFBBBF') . '/', '', $toml);

        return self::parse($toml);
    }


    /**
     * Parses a TOML string to retrieve a hashed array of data.
     *
     * @param (string) $toml TOML formatted string
     *
     * @return (array) Parsed TOML file into array.
     */
    public static function parse($toml)
    {
        $result = array();
        $pointer = & $result;
        $isTableArray = false;

        // Pre-compile
        $toml = self::normalize($toml);

        // Split lines
        $aToml = explode("\n", $toml);

        //foreach($aToml as $line)
        for($ln = 0; $ln < count($aToml); $ln++)
        {
            $line = trim($aToml[$ln]);

            // Skip commented and empty lines
            if(empty($line) || $line[0] == '#')
            {
                continue;
            }

            // Array of Tables
            if(substr($line, 0, 2) == '[[' && substr($line, -2) == ']]')
            {
                // Mark table array
                $isTableArray = true;

                // Set pointer at top level.
                $pointer = & $result;

                $tableName = substr($line, 2, -2);
                //$aTable = explode('.', $tableName);
                $aTable = self::parseTableName($tableName);

                foreach($aTable as $tableName)
                {
                    $tableName = trim($tableName);

                    if($tableName == "")
                    {
                        // Empty table name
                        throw new Exception("Empty table keys aren't allowed");
                    }

                    // Allow quoted table names
                    if($tableName[0] == '"' && substr($tableName,-1) == '"')
                    {
                        $tableName = json_decode($tableName);
                    }
                    elseif(!ctype_alnum(str_replace(array('-', '_', '.'), '', $tableName))) // Check for proper keys
                    {
                        // Invalid table name
                        throw new Exception("Invalid table name: " . $tableName);
                    }

                    if( !isset($pointer[$tableName]) )
                    {
                        // Create array of tables
                        $pointer[$tableName] = array();
                    }

                    // Move pointer forward
                    $pointer = & $pointer[$tableName];
                }
            }
            // Single Tables
            elseif($line[0] == '[' && substr($line, -1) == ']')
            {
                // Unmark table array
                $isTableArray = false;

                // Set pointer at first level.
                $pointer = & $result;

                $tableName = substr($line, 1, -1);
                $tableName = trim($tableName);

                //$aTable = explode('.', $tableName);
                $aTable = self::parseTableName($tableName);
                $last = count($aTable) - 1;

                foreach($aTable as $i => $tableName)
                {
                    if($tableName == "")
                    {
                        // Empty table name
                        throw new Exception("Empty table keys aren't allowed on line " . $line);
                    }

                    // Allow quoted table names
                    if(($tableName[0] == '"' && substr($tableName,-1) == '"') || ($tableName[0] == "'" && substr($tableName,-1) == "'"))
                    {
                        $tableName = json_decode($tableName);
                    }
                    elseif(!ctype_alnum(str_replace(array('-', '_', '.'), '', $tableName))) // Check for proper keys
                    {
                        // Invalid table name
                        throw new Exception("Invalid table name: " . $tableName);
                    }

                    if( !isset($pointer[$tableName]) )
                    {
                        // Create table
                        $pointer[$tableName] = array();
                    }
                    elseif($i == $last)
                    {
                        // Overwrite key
                        throw new Exception('Key overwrite previous keys: ' . $line);
                    }

                    // Move pointer forward
                    $pointer = & $pointer[$tableName];
                }
            }
            // Key = Values
            elseif(strpos($line, '='))
            {
                // Handle table array
                if($isTableArray)
                {
                    // Create new item and point it
                    $pointer[] = array();
                    end($pointer);
                    $pointer =  & $pointer[key($pointer)];

                    // Unmark table array
                    $isTableArray = false;
                }

                $kv = explode('=', $line, 2);

                if(!isset($pointer[ trim($kv[0]) ]))
                {
                    // Multi-line strings
                    if(substr(trim($kv[1]), 0, 3) == '"""')
                    {
                        if(strlen(trim($kv[1])) > 3 && substr(trim($kv[1]), -3) != '"""' || strlen(trim($kv[1])) == 3)
                        {
                            do
                            {
                                $ln++;
                                $kv[1] .= "\n" . $aToml[$ln];
                            }
                            while(strpos($aToml[$ln], '"""') === false);
                        }
                    }

                    // Multi-line literal strings
                    if(substr(trim($kv[1]), 0, 3) == "'''")
                    {
                        if(strlen(trim($kv[1])) > 3 && substr(trim($kv[1]), -3) != "'''" || strlen(trim($kv[1])) == 3)
                        {
                            do
                            {
                                $ln++;
                                $kv[1] .= "\n" . $aToml[$ln];
                            }
                            while(strpos($aToml[$ln], "'''") === false);
                        }
                    }

                    // Set key=value
                    $pointer[ trim($kv[0]) ] = self::parseValue( trim($kv[1]) );
                }
                else
                {
                    throw new Exception('Key overwrite previous keys: ' . $line);
                }
            }
            elseif($line[0] == '[' && substr($line, -1) != ']')
            {
                throw new Exception('Key groups have to be on a line by themselves: ' . $line);
            }
            else
            {
                throw new Exception('Syntax error on: ' . $line);
            }
        }

        return $result;
    }


    /**
     * Performs text modifications in order to normalize the TOML file for the parser.
     * Kind of pre-compiler.
     *
     * @param (string) $toml TOML string.
     *
     * @return (string) Normalized TOML string
     */
    private static function normalize($toml)
    {
        // Cleanup EOL chars.
        $toml = str_replace(array("\r\n", "\n\r"), "\n", $toml);

        // Cleanup TABs
        $toml = str_replace("\t", " ", $toml);

        // Run, char by char.
        $normalized     = '';
        $openString     = false;
        $openLString    = false;
        $openMString    = false;
        $openMLString   = false;
        $openBrackets   = 0;
        $openKeygroup   = false;
        $lineBuffer     = '';

        $strLen = strlen($toml);
        for($i = 0; $i < $strLen; $i++)
        {
            $keep = true;

            if($toml[$i] == '[' && !$openString && !$openLString && !$openMString && !$openMLString)
            {
                // Keygroup or array definition start outside a string
                $openBrackets++;

                // Keygroup
                if($openBrackets == 1 && trim($lineBuffer) == '')
                {
                    $openKeygroup = true;
                }
            }
            elseif($toml[$i] == ']' && !$openString && !$openLString && !$openMString && !$openMLString)
            {
                // Keygroup or array definition end outside a string
                if($openBrackets > 0)
                {
                    $openBrackets--;

                    if($openKeygroup)
                    {
                        $openKeygroup = false;
                    }
                }
                else
                {
                    throw new Exception("Unexpected ']' on: " . $lineBuffer);
                }
            }
            elseif($openBrackets > 0 && $toml[$i] == "\n")
            {
                // Multi-line keygroup definition is not alowed.
                if($openKeygroup)
                {
                    throw new Exception('Multi-line keygroup definition is not allowed on: ' . $lineBuffer);
                }

                // EOLs inside array definition. We don't want them.
                $keep = false;
            }
            elseif(($openString || $openLString) && $toml[$i] == "\n")
            {
                // EOLs inside string should throw error.
                throw new Exception("Multi-line string not allowed on: " . $lineBuffer);
            }
            elseif($toml[$i] == '"' && $toml[$i - 1] != "\\" && !$openLString && !$openMLString) // String handling, allow escaped quotes.
            {
                // Check multi-line strings
                if(substr($toml, $i, 3) == '"""')
                {
                    // Include the token inmediately.
                    $i += 2;
                    $normalized .= '"""';
                    $lineBuffer .= '"""';;
                    $keep = false;

                    $openMString = !$openMString;
                }
                elseif(!$openMString) // Simple strings
                {
                    $openString = !$openString;
                }
            }
            elseif($toml[$i] == "'" && !$openString && !$openMString) // Literal string handling.
            {
                // Check multi-line strings
                if(substr($toml, $i, 3) == "'''")
                {
                    // Include the token inmediately.
                    $i += 2;
                    $normalized .= "'''";
                    $lineBuffer .= "'''";
                    $keep = false;

                    $openMLString = !$openMLString;
                }
                elseif(!$openMLString) // Simple strings
                {
                    $openLString = !$openLString;
                }
            }
            elseif($toml[$i] == "\\" && !in_array($toml[$i+1], array('b', 't', 'n', 'f', 'r', 'u', 'U', '"', "\\", ' ')))
            {
                // Reserved special characters inside strings should produce error
                if($openString)
                {
                    throw new Exception('Reserved special characters inside strings are not allowed: ' . $toml[$i] . $toml[$i+1]);
                }

                // Cleanup escaped new lines and whitespaces from multi-line strings
                if($openMString)
                {
                    while($toml[$i+1] == "\n" || $toml[$i+1] == " ")
                    {
                        $i++;
                        $keep = false;
                    }
                }
            }
            elseif($toml[$i] == '#' && !$openString && !$openKeygroup)
            {
                // Remove comments only at the end of the line. Doesn't catch comments inside array definition.
                while(isset($toml[$i]) && $toml[$i] != "\n")
                {
                    $i++;
                }

                // Last char we know it's EOL.
                $keep = ($openBrackets == 0);
            }

            // Raw Lines
            $lineBuffer .= $toml[$i];
            if($toml[$i] == "\n")
            {
                $lineBuffer = '';
            }

            if($keep)
            {
                $normalized .= $toml[$i];
            }
        }

        // Something went wrong.
        if($openBrackets)
        {
            throw new Exception('Syntax error found on TOML document. Missing closing bracket.');
        }
        if($openString)
        {
            throw new Exception('Syntax error found on TOML document. Missing closing string delimiter.');
        }
        if($openMString)
        {
            throw new Exception('Syntax error found on TOML document. Missing closing multi-line string delimiter.');
        }
        if($openLString)
        {
            throw new Exception('Syntax error found on TOML document. Missing closing literal string delimiter.');
        }
        if($openMLString)
        {
            throw new Exception('Syntax error found on TOML document. Missing closing multi-line literal string delimiter.');
        }
        if($openKeygroup)
        {
            throw new Exception('Syntax error found on TOML document. Missing closing key group delimiter.');
        }

        return $normalized;
    }


    /**
     * Parses TOML table names and returns the hierarchy array of table names.
     *
     * @param (string) $name
     *
     * @return (array) Table names
     */
    private static function parseTableName($name)
    {
        // Init buffer
        $buffer = '';
        $strOpen = false;
        $names = array();

        $strLen = strlen($name);
        for($i = 0; $i < $strLen; $i++)
        {
            if($name[$i] == '"')
            {
                // Toggle strings
                if( !$strOpen || ($strOpen && $name[$i - 1] != "\\") )
                {
                    $strOpen = !$strOpen;
                }
            }
            elseif($name[$i] == '.' && !$strOpen)
            {
                // Save and cleanup buffer. Continue.
                $names[] = $buffer;
                $buffer = '';
                continue;
            }

            // Store char
            $buffer .= $name[$i];
        }

        // Save last buffer
        if($buffer != '') {
            $names[] = $buffer;
        }

        return $names;
    }

    /**
     * Parses TOML value and returns it to be assigned on the hashed array
     *
     * @param (string) $val
     *
     * @return (mixed) Parsed value.
     */
    private static function parseValue($val)
    {
        $parsedVal = null;

        // Cleanup
        $val = trim($val);

        if($val === '')
        {
            throw new Exception('Empty value not allowed');
        }

        // Boolean
        if($val == 'true' || $val == 'false')
        {
            $parsedVal = ($val == 'true');
        }
        // Literal multi-line string
        elseif(substr($val, 0, 3) == "'''" && substr($val, -3) == "'''")
        {
            $parsedVal = substr($val, 3, -3);

            // Trim first newline on multi-line string definition
            if($parsedVal[0] == "\n")
            {
                $parsedVal = substr($parsedVal, 1);
            }
        }
        // Literal string
        elseif($val[0] == "'" && substr($val, -1) == "'")
        {
            // No newlines allowed
            if(strpos($val, "\n") !== false)
            {
                throw new Exception('New lines not allowed on single line string literals.');
            }

            $parsedVal = substr($val, 1, -1);
        }
        // Multi-line string
        elseif(substr($val, 0, 3) == '"""' && substr($val, -3) == '"""')
        {
            $parsedVal = substr($val, 3, -3);

            // Trim first newline on multi-line string definition
            if($parsedVal[0] == "\n")
            {
                $parsedVal = substr($parsedVal, 1);
            }

            // Use json_decode to finally parse the string.
            $parsedVal = json_decode('"' . str_replace("\n", '\n', $parsedVal) . '"');
        }
        // String
        elseif($val[0] == '"' && substr($val, -1) == '"')
        {
            // TOML's specification says it's the same as for JSON format... so
            $parsedVal = json_decode($val);
        }
        // Numbers
        elseif(is_numeric(str_replace('_', '', $val)))
        {
            $val = str_replace('_', '', $val);

            if(is_int($val))
            {
                $parsedVal = (int) $val;
            }
            else
            {
                $parsedVal = (float) $val;
            }
        }
        // Datetime. Parsed to UNIX time value.
        elseif(self::isISODate($val))
        {
            $parsedVal = new DateTime($val);
        }
        // Single line array (normalized)
        elseif($val[0] == '[' && substr($val, -1) == ']')
        {
            $parsedVal = self::parseArray($val);
        }
        // Inline table (normalized)
        elseif($val[0] == '{' && substr($val, -1) == '}')
        {
            $parsedVal = self::parseInlineTable($val);
        }
        else
        {
            throw new Exception('Unknown value type: ' . $val);
        }

        return $parsedVal;
    }


    /**
     * Recursion function to parse all array values through self::parseValue()
     *
     * @param (array) $array
     *
     * @return (array) Parsed array.
     */
    private static function parseArray($val)
    {
        $result = array();
        $openBrackets = 0;
        $openString = false;
        $openLString = false;
        $buffer = '';

        $strLen = strlen($val);
        for($i = 0; $i < $strLen; $i++)
        {
            if($val[$i] == '[' && !$openString && !$openLString)
            {
                $openBrackets++;

                if($openBrackets == 1)
                {
                    // Skip first and last brackets.
                    continue;
                }
            }
            elseif($val[$i] == ']' && !$openString && !$openLString)
            {
                $openBrackets--;

                if($openBrackets == 0)
                {
                    // Allow terminating commas before the closing bracket
                    if(trim($buffer) != '')
                    {
                        $result[] = self::parseValue( trim($buffer) );
                    }

                    if (!self::checkDataType($result))
                    {
                        throw new Exception('Data types cannot be mixed in an array: ' . $buffer);
                    }
                    // Skip first and last brackets. We're finish.
                    return $result;
                }
            }
            elseif($val[$i] == '"' && $val[$i - 1] != "\\" && !$openLString)
            {
                $openString = !$openString;
            }
            elseif($val[$i] == "'"  && !$openString) {
                $openLString = !$openLString;
            }

            if( $val[$i] == ',' && !$openString && !$openLString && $openBrackets == 1)
            {
                $result[] = self::parseValue( trim($buffer) );

                if (!self::checkDataType($result))
                {
                    throw new Exception('Data types cannot be mixed in an array: ' . $buffer);
                }
                $buffer = '';
            }
            else
            {
                $buffer .= $val[$i];
            }
        }

        // If we're here, something went wrong.
        throw new Exception('Wrong array definition: ' . $val);
    }

    /**
     * Parse inline tables into common table array
     */
    private static function parseInlineTable($val)
    {
        // Check valid inline table
        if($val[0] == '{' && substr($val, -1) == '}')
        {
            $val = substr($val, 1, -1);
        }
        else
        {
            throw new Exception('Invalid inline table definition: ' . $val);
        }

        $result = array();
        $openString = false;
        $openLString = false;
        $buffer = '';

        $strLen = strlen($val);
        for($i = 0; $i < $strLen; $i++)
        {
            // Handle strings
            if($val[$i] == '"' && $val[$i - 1] != "\\")
            {
                $openString = !$openString;
            }
            elseif($val[$i] == "'") {
                $openLString = !$openLString;
            }

            if( $val[$i] == ',' && !$openString && !$openLString )
            {
                // Parse buffer
                $kv = explode('=', $buffer, 2);
                $result[trim($kv[0])] = self::parseValue( trim($kv[1]) );

                // Clear buffer
                $buffer = '';
            }
            else
            {
                $buffer .= $val[$i];
            }
        }

        // Parse last buffer
        $kv = explode('=', $buffer, 2);
        $result[trim($kv[0])] = self::parseValue( trim($kv[1]) );

        return $result;
    }

    /**
     * Function that checks the data type of the first and last elements of an array,
     * and returns false if they don't match
     *
     * @param  (array) $array
     *
     * @return boolean
     */
    private static function checkDataType($array)
    {
        if(count($array) <= 1)
        {
            return true;
        }

        $last = count($array) - 1;

        $type = self::getCustomDataType($array[$last]);

        if ($type != self::getCustomDataType($array[0]))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Returns the data type of a variable
     *
     * @param  (mixed) $val
     * @return (string) Data type of value
     */
    private static function getCustomDataType($val)
    {
        if (!is_array($val))
        {
            $type = "date";
        }
        else
        {
            $type = gettype($val);
        }

        return $type;
    }

    /**
     * Return whether the given value is a valid ISODate
     *
     * @param  (string)  $val
     * @return boolean
     */
    public static function isISODate($val)
    {
        if(!is_string($val)) {
            return false;
        }

        // Use DateTime support to check for valid dates.
        try
        {
            $date = new DateTime($val);
        }
        catch(Exception $e)
        {
            return false;
        }

        return true;
    }
}