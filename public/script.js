const dropZone   = document.getElementById("dropZone");
const fileInput  = document.getElementById("fileInput");
const fileInfo   = document.getElementById("fileInfo");
const fileName   = document.getElementById("fileName");
const fileSize   = document.getElementById("fileSize");
const removeFile = document.getElementById("removeFile");
const convertBtn = document.getElementById("convertBtn");
const status     = document.getElementById("status");
const result     = document.getElementById("result");
const textOutput = document.getElementById("textOutput");
const copyBtn    = document.getElementById("copyBtn");
const downloadBtn = document.getElementById("downloadBtn");
const stats      = document.getElementById("stats");

// New option fields
const firstPage  = document.getElementById("firstPage");
const lastPage   = document.getElementById("lastPage");
const resolution = document.getElementById("resolution");
const encoding   = document.getElementById("encoding");
const eolSelect  = document.getElementById("eol");
const nopgbrk    = document.getElementById("nopgbrk");
const cropX      = document.getElementById("cropX");
const cropY      = document.getElementById("cropY");
const cropW      = document.getElementById("cropW");
const cropH      = document.getElementById("cropH");
const userPw     = document.getElementById("userPw");
const ownerPw    = document.getElementById("ownerPw");

let selectedFile = null;

// ─── Collapsible panels ──────────────────────────────────────────
function togglePanel(panelId, btn) {
  const panel = document.getElementById(panelId);
  const isOpen = panel.classList.toggle("open");
  btn.querySelector(".toggle-arrow").textContent = isOpen ? "▴" : "▾";
}

document.getElementById("advancedToggle").addEventListener("click", function () {
  togglePanel("advancedPanel", this);
});
document.getElementById("passwordToggle").addEventListener("click", function () {
  togglePanel("passwordPanel", this);
});

// ─── File size formatter ─────────────────────────────────────────
function formatSize(bytes) {
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
  return (bytes / (1024 * 1024)).toFixed(2) + " MB";
}

// ─── File selection ──────────────────────────────────────────────
function selectFile(file) {
  if (!file || file.type !== "application/pdf") {
    showStatus("Please select a PDF file.", "error");
    return;
  }
  if (file.size > 10 * 1024 * 1024) {
    showStatus("File exceeds the 10 MB size limit.", "error");
    return;
  }
  selectedFile = file;
  fileName.textContent = file.name;
  fileSize.textContent = formatSize(file.size);
  fileInfo.classList.add("visible");
  convertBtn.disabled = false;
  hideStatus();
  result.classList.remove("visible");
}

function clearFile() {
  selectedFile = null;
  fileInput.value = "";
  fileInfo.classList.remove("visible");
  convertBtn.disabled = true;
  result.classList.remove("visible");
}

// ─── Status messages ─────────────────────────────────────────────
function showStatus(msg, type) {
  status.textContent = msg;
  status.className = "status visible " + type;
}
function hideStatus() {
  status.className = "status";
}

// ─── Drop zone events ─────────────────────────────────────────────
dropZone.addEventListener("click", () => fileInput.click());
fileInput.addEventListener("change", (e) => {
  if (e.target.files[0]) selectFile(e.target.files[0]);
});

dropZone.addEventListener("dragover", (e) => {
  e.preventDefault();
  dropZone.classList.add("dragover");
});
dropZone.addEventListener("dragleave", () =>
  dropZone.classList.remove("dragover"),
);
dropZone.addEventListener("drop", (e) => {
  e.preventDefault();
  dropZone.classList.remove("dragover");
  if (e.dataTransfer.files[0]) selectFile(e.dataTransfer.files[0]);
});

removeFile.addEventListener("click", clearFile);

// ─── Convert ─────────────────────────────────────────────────────
convertBtn.addEventListener("click", async () => {
  if (!selectedFile) return;

  convertBtn.classList.add("loading");
  convertBtn.textContent = "Converting…";
  convertBtn.disabled = true;
  hideStatus();
  result.classList.remove("visible");

  const formData = new FormData();
  formData.append("pdf_file", selectedFile);

  // Output mode
  const mode = document.querySelector('input[name="mode"]:checked')?.value || "layout";
  formData.append("mode", mode);

  // Page range
  if (firstPage.value) formData.append("first_page", firstPage.value);
  if (lastPage.value)  formData.append("last_page", lastPage.value);

  // Resolution
  if (resolution.value) formData.append("resolution", resolution.value);

  // Encoding & EOL
  formData.append("encoding", encoding.value);
  if (eolSelect.value) formData.append("eol", eolSelect.value);

  // No page break
  if (nopgbrk.checked) formData.append("nopgbrk", "1");

  // Crop area
  if (cropX.value !== "") formData.append("crop_x", cropX.value);
  if (cropY.value !== "") formData.append("crop_y", cropY.value);
  if (cropW.value !== "") formData.append("crop_w", cropW.value);
  if (cropH.value !== "") formData.append("crop_h", cropH.value);

  // Passwords
  if (userPw.value)  formData.append("user_password", userPw.value);
  if (ownerPw.value) formData.append("owner_password", ownerPw.value);

  try {
    const response = await fetch(window.location.href, {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      showStatus(data.message, "success");
      textOutput.textContent = data.text;
      result.classList.add("visible");

      // Stats
      const chars = data.text.length;
      const words = data.text.split(/\s+/).filter((w) => w.length > 0).length;
      const lines = data.text.split("\n").length;
      stats.innerHTML = `
        <span><strong>${chars.toLocaleString()}</strong> characters</span>
        <span><strong>${words.toLocaleString()}</strong> words</span>
        <span><strong>${lines.toLocaleString()}</strong> lines</span>
      `;

      // Download link
      if (data.download) {
        const ext = data.mode === "htmlmeta" ? ".html" : ".txt";
        downloadBtn.href = "?download=" + encodeURIComponent(data.download);
        downloadBtn.textContent = "💾 Download " + ext;
        downloadBtn.style.display = "inline-flex";
      }
    } else {
      showStatus(data.message, "error");
    }
  } catch (err) {
    showStatus("Network error: " + err.message, "error");
  }

  convertBtn.classList.remove("loading");
  convertBtn.textContent = "Convert to text";
  convertBtn.disabled = false;
});

// ─── Copy ─────────────────────────────────────────────────────────
copyBtn.addEventListener("click", () => {
  navigator.clipboard.writeText(textOutput.textContent).then(() => {
    copyBtn.classList.add("copied");
    copyBtn.textContent = "✓ Copied!";
    setTimeout(() => {
      copyBtn.classList.remove("copied");
      copyBtn.textContent = "📋 Copy";
    }, 2000);
  });
});
