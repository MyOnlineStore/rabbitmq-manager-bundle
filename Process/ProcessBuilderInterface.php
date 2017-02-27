<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Process;

use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\LogicException;

interface ProcessBuilderInterface
{
    /**
     * Adds an unescaped argument to the command string.
     *
     * @param string $argument A command argument
     *
     * @return $this
     */
    public function add($argument);

    /**
     * Adds a prefix to the command string.
     *
     * The prefix is preserved when resetting arguments.
     *
     * @param string|array $prefix A command prefix or an array of command prefixes
     *
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Sets the arguments of the process.
     *
     * Arguments must not be escaped.
     * Previous arguments are removed.
     *
     * @param string[] $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments);

    /**
     * Sets the working directory.
     *
     * @param null|string $cwd The working directory
     *
     * @return $this
     */
    public function setWorkingDirectory($cwd);

    /**
     * Sets whether environment variables will be inherited or not.
     *
     * @param bool $inheritEnv
     *
     * @return $this
     */
    public function inheritEnvironmentVariables($inheritEnv = true);

    /**
     * Sets an environment variable.
     *
     * Setting a variable overrides its previous value. Use `null` to unset a
     * defined environment variable.
     *
     * @param string $name The variable name
     * @param null|string $value The variable value
     *
     * @return $this
     */
    public function setEnv($name, $value);

    /**
     * Adds a set of environment variables.
     *
     * Already existing environment variables with the same name will be
     * overridden by the new values passed to this method. Pass `null` to unset
     * a variable.
     *
     * @param array $variables The variables
     *
     * @return $this
     */
    public function addEnvironmentVariables(array $variables);

    /**
     * Sets the input of the process.
     *
     * @param resource|scalar|\Traversable|null $input The input content
     *
     * @return $this
     *
     * @throws InvalidArgumentException In case the argument is invalid
     */
    public function setInput($input);

    /**
     * Sets the process timeout.
     *
     * To disable the timeout, set this value to null.
     *
     * @param float|null $timeout
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setTimeout($timeout);

    /**
     * Adds a proc_open option.
     *
     * @param string $name The option name
     * @param string $value The option value
     *
     * @return $this
     */
    public function setOption($name, $value);

    /**
     * Disables fetching output and error output from the underlying process.
     *
     * @return $this
     */
    public function disableOutput();

    /**
     * Enables fetching output and error output from the underlying process.
     *
     * @return $this
     */
    public function enableOutput();

    /**
     * Creates a Process instance and returns it.
     *
     * @return ProcessInterface
     *
     * @throws LogicException In case no arguments have been provided
     */
    public function getProcess();
}
