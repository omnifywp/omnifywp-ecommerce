import os
import subprocess
import time
from PIL import Image

edge = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
html_path = os.path.abspath("scratch/render_banner_crisp.html").replace("\\", "/")
url = f"file:///{html_path}"
temp_raw = os.path.abspath("scratch/raw_render_2x.png")

cmd = [
    edge,
    "--headless",
    "--disable-gpu",
    "--force-device-scale-factor=2",
    "--window-size=1544,500",
    f"--screenshot={temp_raw}",
    url
]

print("Launching Edge to capture 2x screenshot...")
subprocess.run(cmd, check=True)
time.sleep(1)

if not os.path.exists(temp_raw):
    print("Error: temp_raw not created!")
    exit(1)

raw_im = Image.open(temp_raw)
print("Captured raw size:", raw_im.size)

# Crop exactly to 3088 x 1000 in case window frame added padding
target_w, target_h = 3088, 1000
if raw_im.size[0] >= target_w and raw_im.size[1] >= target_h:
    cropped_2x = raw_im.crop((0, 0, target_w, target_h))
else:
    cropped_2x = raw_im

# High quality downsampling to 1544x500
im_1544 = cropped_2x.resize((1544, 500), Image.Resampling.LANCZOS)
im_1544.save("scratch/crisp_banner_1544x500.png", "PNG", optimize=True)
print("Saved scratch/crisp_banner_1544x500.png")

# High quality downsampling to 772x250
im_772 = cropped_2x.resize((772, 250), Image.Resampling.LANCZOS)
im_772.save("scratch/crisp_banner_772x250.png", "PNG", optimize=True)
print("Saved scratch/crisp_banner_772x250.png")
