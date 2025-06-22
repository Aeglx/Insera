/**
 * 最终版增强复制功能 - 保留表格格式
 * 支持微信/企业微信/钉钉平台
 */

// 强制使用企业微信模板（需审核确认）
let approvedTemplate = null;

// 平台检测（强制企业微信）
function getPlatform() {
  return 'wxwork'; // 强制使用企业微信模板
}

// 模板审核确认
function approveTemplate(templateName) {
  if (templates.wxwork && templates.wxwork.welcome) {
    approvedTemplate = {
      welcome: templates.wxwork.welcome,
      closing: templates.wxwork.closing,
      lineBreak: templates.wxwork.lineBreak
    };
    console.log('模板已审核确认:', templateName);
    return true;
  }
  console.error('无效的模板名称:', templateName);
  return false;
}

// 初始化时自动审核默认模板
approveTemplate('wxwork');

// 平台定制模板（全部支持emoji）
const templates = {
  // 通用模板（默认）
  default: {
    welcome: [
      "尊敬的客户您好！\uD83D\uDE0A 这是您的续保信息：\n\n",
      "您好！\uD83D\uDC4B 以下是您的可续保车辆明细：\n\n",
      "亲爱的客户\uD83D\uDC8C 您的续保详情如下：\n\n"
    ],
    closing: [
      "\n\n感谢您一直以来的支持！\uD83D\uDE4F",
      "\n\n如有任何问题，请随时联系我们\uD83D\uDCDE",
      "\n\n祝您用车愉快！\uD83D\uDE97"
    ],
    lineBreak: "\n"
  },
  // 微信定制
  wechat: {
    welcome: [
      "尊敬的客户您好！\uD83D\uDE0A\n这是您的续保信息：\n\n",
      "您好！\uD83D\uDC4B\n以下是您的可续保车辆明细：\n\n",
      "亲爱的客户\uD83D\uDC8C\n您的续保详情如下：\n\n"
    ],
    closing: [
      "\n\n感谢您一直以来的支持！\uD83D\uDE4F",
      "\n\n如有任何问题，请随时联系我们\uD83D\uDCDE",
      "\n\n祝您用车愉快！\uD83D\uDE97"
    ],
    lineBreak: "\n"
  },
  // 企业微信定制 - 严格格式模板
  wxwork: {
    // 欢迎语（随机选择一条）
    welcome: [
      "尊敬的客户您好，这是您的保单信息：",
      "您好，保单信息已整理完成：",
      "请查收以下保单详情："
    ],
    
    // 结束语（随机选择一条）
    closing: [
      "以上信息请查收，如有疑问请联系客服。",
      "保单信息仅供参考，具体以合同为准。",
      "如需进一步协助，请随时联系我们。"
    ],
    
    // 固定分隔线
    divider: "─────────────────────",
    
    // 表格处理函数
    processTable: function(tableText) {
      // 强制对齐表格内容
      return tableText.split('\n').map(line => {
        return line.split('|').map(cell => cell.trim()).join(' | ');
      }).join('\n');
    },
    
    lineBreak: "\n"
  },
  // 钉钉定制
  dingtalk: {
    welcome: [
      "尊敬的客户您好！\uD83D\uDE0A\n——————————\n这是您的续保信息：\n",
      "您好！\uD83D\uDC4B\n——————————\n以下是您的可续保车辆明细：\n",
      "亲爱的客户\uD83D\uDC8C\n——————————\n您的续保详情如下：\n"
    ],
    closing: [
      "\n——————————\n感谢您一直以来的支持！\uD83D\uDE4F",
      "\n——————————\n如有任何问题，请随时联系我们\uD83D\uDCDE",
      "\n——————————\n祝您用车愉快！\uD83D\uDE97"
    ],
    lineBreak: "\n"
  }
};

// 获取表格文本内容（确保首行一致）
function getTableText(tableElement) {
  // 1. 验证表格结构
  const headerRow = tableElement.querySelector('thead tr') || 
                   tableElement.querySelector('tr:first-child');
  if (!headerRow) {
    console.error('表格首行未找到');
    return '';
  }

  // 2. 精确获取首行文本
  const headerText = headerRow.innerText.trim();
  
  // 3. 获取完整表格文本
  const tempDiv = document.createElement('div');
  tempDiv.appendChild(tableElement.cloneNode(true));
  let tableText = tempDiv.innerText;
  
  // 4. 验证首行一致性
  const firstLine = tableText.split('\n')[0].trim();
  if (firstLine !== headerText) {
    console.warn('首行不一致，已修正');
    tableText = headerText + '\n' + tableText.substring(tableText.indexOf('\n') + 1);
  }

  // 5. 格式处理
  tableText = tableText.replace(/\n\s+/g, '\n');
  
  // 使用平台特定的换行符
  const platform = getPlatform();
  const templateSet = templates[platform] || templates.default;
  const lineBreak = templateSet.lineBreak;
  
  return tableText.replace(/\n/g, lineBreak);
}

// 增强复制功能
function enhanceTableCopy() {
  document.addEventListener('click', async function(e) {
    // 找到表格容器和复制按钮
    console.log('点击事件触发，目标元素:', e.target);
    const copyBtn = e.target.closest('.copy-table-btn');
    if (!copyBtn) {
      console.log('未找到.copy-table-btn元素');
      return;
    }
    
    e.preventDefault();
    
    const tableContainer = document.querySelector('.renewal-table');
    if (!tableContainer) {
      console.log('未找到.renewal-table元素');
      return;
    }
    
    console.log('成功找到复制按钮和表格容器');
    
    // 获取表格文本
    const tableText = getTableText(tableContainer);
    
    // 使用已审核的企业微信模板
    if (!approvedTemplate) {
      console.error('错误：尚未审核通过任何模板');
      approvedTemplate = {
        welcome: [''],
        closing: [''],
        lineBreak: '\n'
      };
    }
    
    // 验证模板内容
    if (!approvedTemplate.welcome || approvedTemplate.welcome.length === 0) {
      console.error('警告：审核通过的模板欢迎语为空');
      approvedTemplate.welcome = [''];
    }
    if (!approvedTemplate.closing || approvedTemplate.closing.length === 0) {
      console.error('警告：审核通过的模板结束语为空');
      approvedTemplate.closing = [''];
    }
    
    // 随机选择欢迎语和结束语
    const welcome = approvedTemplate.welcome[Math.floor(Math.random() * approvedTemplate.welcome.length)] || '';
    const closing = approvedTemplate.closing[Math.floor(Math.random() * approvedTemplate.closing.length)] || '';
    
    console.log('选择的欢迎语:', welcome);
    console.log('选择的结束语:', closing);
    
    // 严格按格式组合内容
    const finalText = `${welcome}\n\n${tableText}\n\n${closing}`;
    console.log('最终格式:', finalText);
    console.log('最终复制内容:', finalText);
    
    // 执行复制
    try {
      await navigator.clipboard.writeText(finalText);
      showFeedback('复制成功！表格已添加头尾信息', copyBtn);
    } catch (err) {
      console.error('复制失败:', err);
      showFeedback('复制失败，请重试', copyBtn);
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
document.addEventListener('DOMContentLoaded', enhanceTableCopy);