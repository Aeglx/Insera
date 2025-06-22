/**
 * 简单增强复制功能 - 前后添加欢迎语和结束语
 * 自动适配现有复制按钮
 */

// 获取随机模板
async function getRandomTemplate(file) {
  try {
    const response = await fetch(file);
    if (!response.ok) return '';
    const text = await response.text();
    const templates = text.split('\n').filter(line => line.trim());
    return templates[Math.floor(Math.random() * templates.length)] || '';
  } catch (error) {
    console.error('加载模板失败:', error);
    return '';
  }
}

// 增强现有复制功能
function enhanceExistingCopy() {
  // 找到页面上所有复制按钮（假设有copy-btn类）
  document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', async function(e) {
      e.preventDefault();
      
      // 1. 获取原本要复制的内容（假设存储在data-clipboard-text属性中）
      const originalText = this.getAttribute('data-clipboard-text') || this.innerText;
      
      // 2. 获取欢迎语和结束语
      const [welcome, closing] = await Promise.all([
        getRandomTemplate('../templates/welcome_templates.txt'),
        getRandomTemplate('../templates/closing_templates.txt')
      ]);

      // 3. 组装最终文本
      const enhancedText = `${welcome}\n\n${originalText}\n\n${closing}`;

      // 4. 执行复制
      try {
        await navigator.clipboard.writeText(enhancedText);
        alert('复制成功！已添加欢迎语和结束语');
      } catch (err) {
        console.error('复制失败:', err);
        alert('复制失败，请重试');
      }
    });
  });
}

// 页面加载后执行
document.addEventListener('DOMContentLoaded', enhanceExistingCopy);