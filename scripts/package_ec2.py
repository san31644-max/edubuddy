"""Build the app artifact, excluding local keys, sessions, backups and tooling."""
from pathlib import Path
import tarfile

root = Path(__file__).resolve().parents[1]
destination = root / '.deploy' / 'keducation-ec2-app.tar.gz'
directories = ['admin', 'assets', 'chatbot', 'includes', 'logo', 'ol-kuppiya', 'parent', 'referral', 'student', 'uploads', 'downloads']
blocked_suffixes = {'.pem', '.key', '.log', '.sql', '.pyc'}
count = total = 0
with tarfile.open(destination, 'w:gz') as archive:
    candidates = list(root.glob('*.php')) + [root / name for name in ['manifest.json', 'service-worker.js', '.user.ini']]
    for directory in directories:
        candidates.extend((root / directory).rglob('*'))
    for path in candidates:
        if not path.is_file():
            continue
        relative = path.relative_to(root)
        if path.suffix.lower() in blocked_suffixes or any(part.startswith('.') for part in relative.parts if part != '.htaccess'):
            continue
        if 'runtime' in relative.parts or '__pycache__' in relative.parts or path.name.startswith('sess_'):
            continue
        if relative.as_posix() == 'admin/real-db.php':
            continue
        archive.add(path, arcname=relative.as_posix(), recursive=False)
        count += 1
        total += path.stat().st_size
print(f'Packaged {count} files ({total / 1048576:.1f} MiB uncompressed). Archive: {destination.name}')
with tarfile.open(destination) as archive:
    names = archive.getnames()
    assert not any(name.endswith(('.pem', '.sql')) or '/runtime/' in name for name in names)
    assert 'index.php' in names and 'admin/dashboard.php' in names
print('Archive verified: no private keys, SQL exports, or runtime sessions/secrets.')
