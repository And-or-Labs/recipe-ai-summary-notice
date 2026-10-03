"""Produce a reproducible WordPress upload archive containing only plugin files."""
from pathlib import Path
from zipfile import ZipFile, ZipInfo, ZIP_DEFLATED
root = Path(__file__).resolve().parents[1]
source = root / 'plugin' / 'recipe-ai-summary-notice'
target = root / 'artifacts' / 'recipe-ai-summary-notice-1.2.0.zip'
target.parent.mkdir(exist_ok=True)
with ZipFile(target, 'w', compression=ZIP_DEFLATED) as archive:
    for path in sorted(source.rglob('*')):
        if not path.is_file() or path.name.startswith('.'):
            continue
        info = ZipInfo('recipe-ai-summary-notice/' + str(path.relative_to(source)), (2026, 10, 2, 0, 0, 0))
        info.compress_type = ZIP_DEFLATED
        info.external_attr = 0o100644 << 16
        archive.writestr(info, path.read_bytes())
print(target)
