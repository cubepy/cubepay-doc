#!/usr/bin/env python3
"""
Verifies internal Markdown links across the repo:

  - Every relative link/image target (./foo.md, ../bar.png, ...) resolves to
    a real file on disk.
  - Every #anchor fragment (same-file or cross-file, on a .md target)
    matches an actual heading in the target file, using GitHub's heading
    slug rules (lowercase, strip punctuation/emoji, spaces -> hyphens).

Skips http(s)/mailto links and anything without a leading ./ or ../, since
this repo's docs conventions (see MAINTAINERS.md) use relative links for
same-repo cross-references.

Exit code is non-zero if any broken link or anchor is found.
"""
import os
import re
import sys

REPO_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

LINK_RE = re.compile(r'!?\[[^\]]*\]\(([^)]+)\)')
HEADING_RE = re.compile(r'^(#{1,6})\s+(.+?)\s*$', re.MULTILINE)


def slugify(heading):
    text = heading.strip().lower()
    # Drop anything that's not a "word" char (unicode-aware, so Persian
    # letters survive), whitespace, or hyphen -- this removes emoji and
    # punctuation the same way GitHub's own heading slugger does.
    text = re.sub(r'[^\w\s-]', '', text, flags=re.UNICODE)
    text = re.sub(r'\s+', '-', text)
    return text


def find_markdown_files():
    for root, dirs, files in os.walk(REPO_ROOT):
        dirs[:] = [d for d in dirs if d != '.git']
        for f in files:
            if f.endswith('.md'):
                yield os.path.join(root, f)


def headings_of(path):
    text = open(path, encoding='utf-8').read()
    return {slugify(h) for _, h in HEADING_RE.findall(text)}


def main():
    md_files = list(find_markdown_files())
    heading_cache = {}
    errors = []

    for path in md_files:
        rel_path = os.path.relpath(path, REPO_ROOT)
        text = open(path, encoding='utf-8').read()
        for m in LINK_RE.finditer(text):
            target = m.group(1).strip()
            if target.startswith(('http://', 'https://', 'mailto:', '#')):
                anchor_only = target.startswith('#')
                if not anchor_only:
                    continue
                file_part, frag = path, target[1:]
            elif target.startswith(('./', '../')):
                file_part, _, frag = target.partition('#')
                file_part = os.path.normpath(os.path.join(os.path.dirname(path), file_part))
            else:
                continue

            if target.startswith(('./', '../')):
                if not os.path.exists(file_part):
                    errors.append(f"{rel_path}: broken link -> {target}")
                    continue

            if frag and file_part.endswith('.md') and os.path.exists(file_part):
                if file_part not in heading_cache:
                    heading_cache[file_part] = headings_of(file_part)
                if frag not in heading_cache[file_part]:
                    target_rel = os.path.relpath(file_part, REPO_ROOT)
                    errors.append(f"{rel_path}: broken anchor -> #{frag} (not found in {target_rel})")

    if errors:
        print(f"Found {len(errors)} broken link(s)/anchor(s):\n")
        for e in errors:
            print(f"  - {e}")
        return 1

    print(f"OK: checked {len(md_files)} Markdown files, no broken relative links or anchors.")
    return 0


if __name__ == '__main__':
    sys.exit(main())
