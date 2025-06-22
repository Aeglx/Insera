/**
 * 增强复制功能 - 支持表情的欢迎语/结束语
 * 兼容微信/企业微信/钉钉
 */

// 全局缓存模板
let messageTemplates = null;

// 加载模板
async function loadTemplates() {
  try {
    const response = await fetch('templates/messages.json');
    if (!response.ok) throw new Error('模板加载失败');
    messageTemplates = await response.json();
  } catch (error) {
    console.error('加载模板失败:', error);
    // 默认模板
    messageTemplates = {
      welcome: ["尊敬的客户您好！这是您的续保车辆信息："],
      closing: ["感谢您一直以来的支持！"]
    };
  }
}

// 获取随机消息
function getRandomMessage(type) {
  if (!messageTemplates || !messageTemplates[type]) return '';
  const messages = messageTemplates[type];
  return messages[Math.floor(Math.random() * messages.length)];
}

// 增强复制功能
function enhanceCopyButtons() {
  document.addEventListener('click', async function(e) {
    const btn = e.target.closest('[data-copy-target]');
    if (!btn) return;

    e.preventDefault();
    
    // 加载模板(首次)
    if (!messageTemplates) await loadTemplates();

    // 获取原始内容
    const target = document.querySelector(btn.dataset.copyTarget);
    const originalText = target?.innerText || '';

    // 组装内容
    const content = [
      getRandomMessage('welcome'),
      '',
      originalText,
      '',
      getRandomMessage('closing')
    ].join('\n');

    // 复制到剪贴板
    try {
      await navigator.clipboard.writeText(content);
      showFeedback('复制成功！', btn);
    } catch (err) {
      console.error('复制失败:', err);
      showFeedback('复制失败，请重试', btn);
    }
  });
}

// 显示反馈
function showFeedback(message, element) {
  const feedback = document.createElement('div');
  feedback.className = 'copy-feedback';
  feedback.textContent = message;
  
  element.appendChild(feedback);
  setTimeout(() => feedback.remove(), 2000);
}

// 初始化
document.addEventListener('DOMContentLoaded', () => {
  enhanceCopyButtons();
});