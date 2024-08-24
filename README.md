# TimeLock

**TimeLock** is a command-line tool designed to help developers identify files in a Git repository that have remained unchanged since a specific date. It can be configured to exclude specific authors, paths, and file types using regex patterns.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Examples](#examples)
  - [Example Output](#example-output)
- [Running Tests](#running-tests)
- [Contributing](#contributing)
- [License](#license)

## Features

- **File Detection**: Detects files in a Git repository that have not changed since a specified date.
- **Author Exclusion**: Exclude files from specific authors.
- **Path and Regex Exclusions**: Exclude files based on paths or regex patterns.
- **Output Formats**: Supports both table and JSON output formats.
- **Customizable**: Easily extendable to support other version control systems.

## Installation

To install **TimeLock** via Composer, run the following command:

```bash
composer require timelock/timelock
```

After installation, the `timelock` binary will be available in the `vendor/bin` directory.

## Usage

The `check` command is the main CLI tool provided by TimeLock. Below is an example of how to use it:

```bash
vendor/bin/timelock check --config=path/to/timelock.yml
```

### Command-Line Options

- `path` (optional): The directory path to check. Defaults to the current directory.
- `--config` (optional): The path to the configuration file. Defaults to `timelock.yml` in the current directory.
- `--output-format` (optional): The output format (`table` or `json`). Defaults to `table`.

## Configuration

TimeLock is configured using a YAML file (`timelock.yml`). Below is an example configuration file:

```yaml
since: '5 years ago'               # Files unchanged since this date will be flagged
excludeAuthors:                    # Authors to exclude from the check
  - 'John Doe'
  - 'Jane Smith'
exclude:                           # Paths to exclude from the check
  - 'vendor/'
  - 'tests/'
excludeRegex:                      # Regex patterns to exclude from the check
  - '/.*Controller\.php$/'
vcs: 'git'                         # Version control system to use (default is 'git')
```

### Configuration Options

- `since`: A date string or timestamp to check files against.
- `excludeAuthors`: A list of author names to exclude.
- `exclude`: A list of paths to exclude.
- `excludeRegex`: A list of regex patterns to exclude specific files.
- `vcs`: The version control system to use. Currently supports `git`.

## Examples

### Basic Usage

Check for files unchanged in the current directory:

```bash
vendor/bin/timelock check
```

### Custom Configuration

Use a specific configuration file:

```bash
vendor/bin/timelock check --config=/path/to/your-config.yml
```

### JSON Output

Get the output in JSON format:

```bash
vendor/bin/timelock check --output-format=json
```

### Example Output

Here’s an example of what the output might look like when using the `table` format:

```bash
vendor/bin/timelock check --config=path/to/timelock.yml
```

Output:

```
+------------+-----------+---------------------+---------+
| File       | Author    | Last Modified       | Changes |
+------------+-----------+---------------------+---------+
| file1.txt  | John Doe  | 2017-06-01 12:00:00 | 1       |
+------------+-----------+---------------------+---------+
| file2.txt  | Jane Doe  | 2019-03-15 15:30:00 | 3       |
+------------+-----------+---------------------+---------+

Check completed.
Execution time: 0.42 seconds
```

In this example:

- `File`: The name of the file that has been unchanged since the specified date.
- `Author`: The author of the last commit to that file.
- `Last Modified`: The date and time when the file was last modified.
- `Changes`: The number of changes made to the file.

## Running Tests

To run the test suite, use PHPUnit. If you haven’t installed PHPUnit globally, you can use the local installation:

```bash
composer test
```

The tests are located in the `tests` directory and cover the core functionality of the TimeLock tool, including Git integration and configuration handling.

## Contributing

We welcome contributions! Here’s how you can get involved:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Make your changes.
4. Commit your changes (`git commit -m 'Add some feature'`).
5. Push to the branch (`git push origin feature/your-feature`).
6. Open a pull request.

Please make sure to write tests for your changes and ensure all existing tests pass.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.