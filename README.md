# SSync

A command-line tool for managing selective synchronization between two
directories using Unison.

SSync (Selective Sync) acts as a wrapper around the powerful `unison` file
synchronizer, allowing you to selectively exclude certain files from the
synchronization process. This is achieved by maintaining a "diff store" of
changes you have already reviewed.

When you run the `sync` command, it first verifies that the current state of the
files matches their stored diffs. Only files whose diffs remain unchanged are
then ignored by `unison`, giving you a clean slate to resolve conflicts on the
remaining files, including those whose diffs have changed.

## Features

- Selectively ignore files during synchronization.
- Side-by-side diff comparison using Difftastic.
- Stores diffs for later review.
- Command-line interface with auto-completion.

## Installation

1. Clone the repository.
2. Run `composer install`.
3. Create configuration files in the `config` directory (see below).
4. Install Unison for file synchronization.
5. (Optional) Install Difftastic for better diff visualization.
6. (Optional) Install Bat for enhanced diff display.

## Configuration Files

To create a new configuration, create a new directory inside the `config`
directory. The name of this directory will be your configuration name.

Inside this new directory, create a `config.php` file that returns a PHP array
with the following keys:

- `root1`: The absolute path to the first directory.
- `root2`: The absolute path to the second directory.

Example: `config/my-project/config.php`

```php
<?php

return [
    'root1' => '/path/to/your/first/directory',
    'root2' => '/path/to/your/second/directory',
];
```

## Commands

Commands are run as `ssync <command> [arguments] [options]`. Examples use
`my-project` as the configuration name.

### add

Adds a file to the diff store. This command computes the difference between the
file in `root1` and `root2` and saves it as a patch file.

Usage: `ssync add <config> <file>`

Examples:

- `ssync add my-project path/to/file.txt` – Adds `file.txt` to the diff store.

### cat

Displays the content of a diff from the diff store.

Usage: `ssync cat <config> <file>`

Examples:

- `ssync cat my-project path/to/file.txt` – Shows the stored diff for
  `file.txt`.

Notes: Uses `bat` for syntax-highlighted output if available.

### diff

Shows the live difference between the two versions of a file.

Usage: `ssync diff <config> <file>`

Examples:

- `ssync diff my-project path/to/file.txt` – Compares the two versions of
  `file.txt`.

Options: `--inline|-i` (use difftastic's inline display mode).

Notes: Uses `difftastic` for output if available.

### ls

Lists all the files currently in the diff store for a given configuration.

Usage: `ssync ls <config>`

Examples:

- `ssync ls my-project` – Lists all files in the `my-project` diff store.

### rm

Removes a file from the diff store.

Usage: `ssync rm <config> <file>`

Examples:

- `ssync rm my-project path/to/file.txt` – Removes the diff for `file.txt`.

### sync

Starts the `unison-gui` to synchronize the two directories, ignoring files from
the diff store.

Usage: `ssync sync <config> [external_unison_args...]`

The main purpose of this command is to tell `unison` to ignore the files you
have already reviewed. Before the synchronization starts, `ssync` performs a
check for every file in the diff store:

1. It re-calculates the current difference between the file in `root1` and
   `root2`.
2. It compares this new diff with the one saved in the diff store.
   - If the diffs are **identical**, the file is added to a temporary ignore
     list for `unison`.
   - If the diffs are **different**, it means the file has changed since you
     last added it. The file is **not** ignored, and `unison` will show it as a
     change to be synchronized.

This ensures that you only hide the changes you have already approved, and new
changes are always presented for your review in the Unison UI.

Examples:

- `ssync sync my-project` – Starts Unison for the `my-project` config.
- `ssync sync my-project -auto` – Passes the `-auto` flag to Unison to accept
  non-conflicting changes automatically.

## Versioning

Uses semantic versioning from commit messages: `fix:` (patch), `feat:` (minor),
`!:` (major breaking).

## Auto-completion Support

Supports completion for configs, files and options. Run `ss completion --help`
for setup.

## Requirements

- PHP 8.1+
- Composer
- Unison (for the `sync` command)
- Difftastic (optional, for the `diff` command)
- Bat (optional, for the `cat` command)

## Contributing

Submit pull requests. Ensure tests pass.

## License

BSD Zero Clause License. See LICENSE file.
