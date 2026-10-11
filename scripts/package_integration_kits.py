"""Deterministic public kit archive; --check verifies source/download consistency."""
from pathlib import Path
import io, zipfile, hashlib, sys
root = Path(__file__).resolve().parents[1]
buffer = io.BytesIO()
with zipfile.ZipFile(buffer, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
    for path in sorted((root / 'integrations/kits').rglob('*'), key=lambda p: p.relative_to(root).as_posix()):
        if not path.is_file() or '__pycache__' in path.parts:
            continue
        name = 'CubePay-integration-kits/' + path.relative_to(root / 'integrations/kits').as_posix()
        info = zipfile.ZipInfo(name, date_time=(2026, 10, 11, 0, 0, 0))
        info.create_system = 3
        info.compress_type = zipfile.ZIP_DEFLATED
        info.external_attr = 0o100644 << 16
        body = path.read_bytes()
        # Source files are UTF-8 text; ZIP guide links use the canonical public docs.
        if path.suffix == '.md':
            import re, posixpath
            def absolute(match):
                target = match.group(1)
                resolved = posixpath.normpath(str(path.relative_to(root).parent).replace('\\', '/') + '/' + target)
                return '](' + 'https://github.com/cubepy/cubepay-doc/blob/main/' + resolved + ')'
            body = re.sub(r'\]\((\.{1,2}/[^)]+)\)', absolute, body.decode('utf-8')).encode('utf-8')
        archive.writestr(info, body)
output = root / 'downloads/CubePay-integration-kits.zip'
data = buffer.getvalue()
checksum = hashlib.sha256(data).hexdigest() + '  CubePay-integration-kits.zip\n'
if '--check' in sys.argv:
    # Compression bytes can differ by Python/zlib version. Verify every payload,
    # exact file inventory, CRCs, and the checksum of the actual distributed ZIP.
    actual = output.read_bytes()
    with zipfile.ZipFile(io.BytesIO(actual)) as published, zipfile.ZipFile(io.BytesIO(data)) as expected:
        assert published.testzip() is None, 'Corrupt kit archive'
        assert published.namelist() == expected.namelist(), 'Archive file list differs from source'
        for name in expected.namelist():
            assert published.read(name) == expected.read(name), 'Archive differs from source: ' + name
    assert output.with_suffix('.sha256').read_text() == hashlib.sha256(actual).hexdigest() + '  CubePay-integration-kits.zip\n'
else:
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_bytes(data)
    output.with_suffix('.sha256').write_text(checksum, encoding='utf-8')
print('Integration kit archive matches source' if '--check' in sys.argv else 'Integration kit archive built')
