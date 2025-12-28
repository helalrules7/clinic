---
description: Full deployment pipeline: Optimize images, update references, clean local, build, clean remote, and deploy.
---

1. Optimize images
// turbo
```bash
python3 optimize_images.py
```

2. Update code references to use optimized images
// turbo
```bash
python3 replace_new_refs.py
```

3. Clean unused local unoptimized images
// turbo
```bash
python3 cleanup_local.py
```

4. Build the project
// turbo
```bash
npm run build
```


6. Upload new build to remote server
// turbo
```bash
rsync -avz -e "ssh -i /var/www/html/clinic/keys/OpenSSHPrivate -o StrictHostKeyChecking=no" dist/ root@45.93.138.184:/home/AhmedHelal/web/hclinic.clinic/public_html/docs/opth
```

7. Fix remote permissions
// turbo
```bash
ssh -i /var/www/html/clinic/keys/OpenSSHPrivate -o StrictHostKeyChecking=no root@45.93.138.184 "chown -R AhmedHelal:AhmedHelal /home/AhmedHelal/web/hclinic.clinic/public_html/docs/opth"
```
