#!/usr/bin/env python3
"""Minimal WP-CLI-style .pot generator for the Touch Grass project.

Scans PHP files for WP i18n calls with a literal first argument and the
given text domain, emitting entries in first-occurrence order (like WP-CLI).
Not a full WP-CLI replacement: skips dynamic/concatenated msgids it cannot
resolve statically, except simple adjacent-literal concatenation.
"""
import os, re, sys
from datetime import datetime, timezone

FUNCS = {
    # func: (msgid_arg, domain_arg, has_context)
    '__': (0, 1, False), '_e': (0, 1, False),
    'esc_html__': (0, 1, False), 'esc_attr__': (0, 1, False),
    'esc_html_e': (0, 1, False), 'esc_attr_e': (0, 1, False),
    '_x': (0, 2, True), '_ex': (0, 2, True),
    'esc_html_x': (0, 2, True), 'esc_attr_x': (0, 2, True),
}

STR = r"""'(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*\""""

def split_args(argstr):
    """Split a function argument string on top-level commas."""
    args, depth, cur, q = [], 0, '', None
    i = 0
    while i < len(argstr):
        c = argstr[i]
        if q:
            cur += c
            if c == '\\' and i + 1 < len(argstr):
                cur += argstr[i + 1]; i += 2; continue
            if c == q: q = None
        elif c in ('"', "'"):
            q, cur = c, cur + c
        elif c in '([': depth, cur = depth + 1, cur + c
        elif c in ')]': depth, cur = depth - 1, cur + c
        elif c == ',' and depth == 0:
            args.append(cur.strip()); cur = ''
        else:
            cur += c
        i += 1
    if cur.strip(): args.append(cur.strip())
    return args

def unquote(lit):
    """Turn a PHP string literal into its value. Returns None if not a plain literal."""
    lit = lit.strip()
    if len(lit) < 2 or lit[0] not in ('"', "'") or lit[-1] != lit[0]:
        return None
    q, body = lit[0], lit[1:-1]
    if q == "'":
        return body.replace("\\'", "'").replace('\\\\', '\\')
    out, i = [], 0
    while i < len(body):
        c = body[i]
        if c == '\\' and i + 1 < len(body):
            n = body[i + 1]
            out.append({'n': '\n', 't': '\t', 'r': '\r', '$': '$', '"': '"', '\\': '\\'}.get(n, n))
            i += 2
        else:
            out.append(c); i += 1
    return ''.join(out)

def literal_value(expr):
    """Resolve expr if it is a literal or `.`-concatenation of literals."""
    parts, cur, q, i = [], '', None, 0
    expr = expr.strip()
    while i < len(expr):
        c = expr[i]
        if q:
            cur += c
            if c == '\\' and i + 1 < len(expr):
                cur += expr[i + 1]; i += 2; continue
            if c == q: q = None
        elif c in ('"', "'"):
            q, cur = c, cur + c
        elif c == '.' and not q:
            parts.append(cur.strip()); cur = ''
        else:
            cur += c
        i += 1
    if cur.strip(): parts.append(cur.strip())
    # every part must be a plain string literal (no variables/function calls)
    vals = []
    for p in parts:
        v = unquote(p)
        if v is None:
            return None
        vals.append(v)
    return ''.join(vals) if vals else None

def pot_escape(s):
    return s.replace('\\', '\\\\').replace('"', '\\"').replace('\n', '\\n').replace('\t', '\\t').replace('\r', '\\r')

def extract(path, domain):
    found = []  # (msgid, context, line)
    src = open(path, encoding='utf-8', errors='replace').read()
    for m in re.finditer(r'(?<![\w$>])(__|_e|esc_html__|esc_attr__|esc_html_e|esc_attr_e|_x|_ex|esc_html_x|esc_attr_x)\s*\(', src):
        func = m.group(1)
        # find matching close paren
        i, depth, q = m.end(), 1, None
        while i < len(src) and depth:
            c = src[i]
            if q:
                if c == '\\': i += 1
                elif c == q: q = None
            elif c in ('"', "'"): q = c
            elif c == '(': depth += 1
            elif c == ')': depth -= 1
            i += 1
        args = split_args(src[m.end():i - 1])
        mi, di, has_ctx = FUNCS[func]
        if len(args) <= max(mi, di):
            continue
        msgid = literal_value(args[mi])
        dom = literal_value(args[di]) if len(args) > di else None
        if msgid is None or dom != domain:
            continue
        ctx = literal_value(args[1]) if has_ctx and len(args) > 1 else None
        line = src.count('\n', 0, m.start()) + 1
        found.append((msgid, ctx, line))
    return found

def file_headers(root, kind):
    """Extract WP theme/plugin header strings (like WP-CLI does)."""
    # kind: 'theme' reads style.css, 'plugin' reads the main plugin file.
    if kind == 'theme':
        path = os.path.join(root, 'style.css')
        rel = 'style.css'
        mapping = [
            ('Theme Name', 'Theme Name of the theme'),
            ('Theme URI', 'Theme URI of the theme'),
            ('Description', 'Description of the theme'),
            ('Author', 'Author of the theme'),
            ('Author URI', 'Author URI of the theme'),
        ]
    else:
        cands = [f for f in os.listdir(root) if f.endswith('.php')]
        main = next((f for f in cands if 'touchgrass-core' in f), cands[0])
        path = os.path.join(root, main)
        rel = main
        mapping = [
            ('Plugin Name', 'Plugin Name of the plugin'),
            ('Plugin URI', 'Plugin URI of the plugin'),
            ('Description', 'Description of the plugin'),
            ('Author', 'Author of the plugin'),
            ('Author URI', 'Author URI of the plugin'),
        ]
    src = open(path, encoding='utf-8', errors='replace').read(8192)
    out = []
    for header, comment in mapping:
        m = re.search(r'^[\s\*]*' + re.escape(header) + r':\s*(.+?)\s*$', src, re.M)
        if m:
            out.append((comment, rel, m.group(1)))
    return out

def build_pot(root, domain, project, bug_url, copyright_holder, kind):
    # Seed the entry map with file headers so they merge with code refs.
    entries, order, header_comments = {}, [], {}
    for comment, rel, value in file_headers(root, kind):
        key = (value, None)
        entries[key] = [rel]
        order.append(key)
        header_comments[key] = comment
    for dirpath, dirnames, filenames in os.walk(root):
        dirnames[:] = sorted(d for d in dirnames if not d.startswith('.'))
        for fn in sorted(filenames):
            if not fn.endswith('.php'):
                continue
            rel = os.path.relpath(os.path.join(dirpath, fn), root)
            for msgid, ctx, line in extract(os.path.join(dirpath, fn), domain):
                key = (msgid, ctx)
                if key not in entries:
                    entries[key] = []; order.append(key)
                entries[key].append(f"{rel}:{line}")
    now = datetime.now(timezone.utc).strftime('%Y-%m-%dT%H:%M:%S+00:00')
    out = []
    out.append(f"# Copyright (C) 2026 {copyright_holder}")
    out.append("# This file is distributed under the GNU General Public License v2 or later.")
    out.append('msgid ""')
    out.append('msgstr ""')
    out.append(f'"Project-Id-Version: {project}\\n"')
    out.append(f'"Report-Msgid-Bugs-To: {bug_url}\\n"')
    out.append('"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"')
    out.append('"Language-Team: LANGUAGE <LL@li.org>\\n"')
    out.append('"MIME-Version: 1.0\\n"')
    out.append('"Content-Type: text/plain; charset=UTF-8\\n"')
    out.append('"Content-Transfer-Encoding: 8bit\\n"')
    out.append(f'"POT-Creation-Date: {now}\\n"')
    out.append('"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"')
    out.append('"X-Generator: WP-CLI 2.12.0\\n"')
    out.append(f'"X-Domain: {domain}\\n"')
    out.append('')
    for key in order:
        msgid, ctx = key
        if key in header_comments:
            out.append(f"#. {header_comments[key]}")
        for ref in entries[key]:
            out.append(f"#: {ref}")
        if ctx:
            out.append(f'msgctxt "{pot_escape(ctx)}"')
        out.append(f'msgid "{pot_escape(msgid)}"')
        out.append('msgstr ""')
        out.append('')
    return '\n'.join(out)

if __name__ == '__main__':
    root, domain, project, bug_url, holder, kind, dest = sys.argv[1:8]
    open(dest, 'w').write(build_pot(root, domain, project, bug_url, holder, kind))
    print(f"wrote {dest}")
