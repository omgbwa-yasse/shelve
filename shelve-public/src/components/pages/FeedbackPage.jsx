import React, { useState } from 'react';
import { useMutation } from 'react-query';
import { toast } from 'react-toastify';
import shelveApi from '../../services/shelveApi';
import { validateEmail } from '../../utils/validators';
import Loading from '../common/Loading';
import { Button, Input, TextArea, Select, Radio } from '../forms/FormComponents';
import {
  PageContainer,
  PageHeader,
  PageTitle,
  PageSubtitle,
  FiltersSection,
  FilterGroup,
  FilterLabel
} from '../common/PageComponents';

const FeedbackPage = () => {
  const [formData, setFormData] = useState({
    type: 'suggestion',
    subject: '',
    email: '',
    name: '',
    message: '',
    page_url: window.location.href,
    user_agent: navigator.userAgent,
    rating: '',
    category: '',
    priority: 'normal'
  });

  const [errors, setErrors] = useState({});
  const [isSubmitted, setIsSubmitted] = useState(false);

  // Submit feedback mutation
  const submitFeedbackMutation = useMutation(
    (data) => shelveApi.submitFeedback(data),
    {
      onSuccess: () => {
        toast.success('Votre commentaire a été envoyé avec succès !');
        setIsSubmitted(true);
        setFormData({
          type: 'suggestion',
          subject: '',
          email: '',
          name: '',
          message: '',
          page_url: window.location.href,
          user_agent: navigator.userAgent,
          rating: '',
          category: '',
          priority: 'normal'
        });
        setErrors({});
      },
      onError: (error) => {
        console.error('Error submitting feedback:', error);
        if (error.response?.data?.errors) {
          setErrors(error.response.data.errors);
        } else {
          toast.error('Erreur lors de l\'envoi du commentaire. Veuillez réessayer.');
        }
      }
    }
  );

  // Form validation
  const validateForm = () => {
    const newErrors = {};

    if (!formData.type) {
      newErrors.type = 'Le type de commentaire est requis';
    }

    if (!formData.subject || formData.subject.trim().length < 3) {
      newErrors.subject = 'Le sujet doit contenir au moins 3 caractères';
    }

    if (!formData.email || !validateEmail(formData.email)) {
      newErrors.email = 'Une adresse email valide est requise';
    }

    if (!formData.name || formData.name.trim().length < 2) {
      newErrors.name = 'Le nom doit contenir au moins 2 caractères';
    }

    if (!formData.message || formData.message.trim().length < 10) {
      newErrors.message = 'Le message doit contenir au moins 10 caractères';
    }

    if (formData.type === 'rating' && !formData.rating) {
      newErrors.rating = 'Une note est requise pour ce type de commentaire';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // Handle form submission
  const handleSubmit = (e) => {
    e.preventDefault();

    if (!validateForm()) {
      toast.error('Veuillez corriger les erreurs dans le formulaire');
      return;
    }

    submitFeedbackMutation.mutate(formData);
  };

  // Handle input changes
  const handleChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));

    // Clear specific error when user starts typing
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };

  // Reset form
  const resetForm = () => {
    setIsSubmitted(false);
    setFormData({
      type: 'suggestion',
      subject: '',
      email: '',
      name: '',
      message: '',
      page_url: window.location.href,
      user_agent: navigator.userAgent,
      rating: '',
      category: '',
      priority: 'normal'
    });
    setErrors({});
  };

  if (isSubmitted) {
    return (
      <PageContainer>
        <div style={{ maxWidth: '32rem', margin: '0 auto' }}>
          <div style={{
            backgroundColor: 'white',
            borderRadius: '0.5rem',
            boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1)',
            border: '1px solid #e5e7eb',
            padding: '2rem',
            textAlign: 'center'
          }}>
            <div style={{ color: '#10b981', marginBottom: '1rem' }}>
              <svg style={{ margin: '0 auto', height: '4rem', width: '4rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold', color: '#111827', marginBottom: '1rem' }}>
              Merci pour votre commentaire !
            </h2>
            <p style={{ color: '#6b7280', marginBottom: '1.5rem' }}>
              Votre message a été envoyé avec succès. Nous examinerons votre commentaire et vous répondrons si nécessaire.
            </p>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem', justifyContent: 'center' }}>
              <Button
                onClick={resetForm}
                variant="outline"
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
              >
                <svg style={{ height: '1rem', width: '1rem', marginRight: '0.5rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
                Envoyer un autre commentaire
              </Button>
              <Button
                onClick={() => window.history.back()}
                variant="primary"
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
              >
                <svg style={{ height: '1rem', width: '1rem', marginRight: '0.5rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
              </Button>
            </div>
          </div>
        </div>
      </PageContainer>
    );
  }

  return (
    <PageContainer>
      <div style={{ maxWidth: '32rem', margin: '0 auto' }}>
        <PageHeader>
          <PageTitle>Commentaires et suggestions</PageTitle>
          <PageSubtitle>
            Votre avis nous intéresse ! Partagez vos commentaires, suggestions ou signalez des problèmes.
          </PageSubtitle>
        </PageHeader>

        {/* Feedback form */}
        <FiltersSection>
          <form onSubmit={handleSubmit} noValidate>
            {/* Type of feedback */}
            <FilterGroup style={{ marginBottom: '1.5rem' }}>
              <FilterLabel>Type de commentaire *</FilterLabel>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.75rem' }}>
                <Radio
                  name="feedback-type"
                  value="suggestion"
                  checked={formData.type === 'suggestion'}
                  onChange={(checked) => checked && handleChange('type', 'suggestion')}
                  label="Suggestion d'amélioration"
                />
                <Radio
                  name="feedback-type"
                  value="bug"
                  checked={formData.type === 'bug'}
                  onChange={(checked) => checked && handleChange('type', 'bug')}
                  label="Signalement de problème"
                />
                <Radio
                  name="feedback-type"
                  value="rating"
                  checked={formData.type === 'rating'}
                  onChange={(checked) => checked && handleChange('type', 'rating')}
                  label="Évaluation générale"
                />
                <Radio
                  name="feedback-type"
                  value="question"
                  checked={formData.type === 'question'}
                  onChange={(checked) => checked && handleChange('type', 'question')}
                  label="Question"
                />
              </div>
              {errors.type && (
                <p style={{ marginTop: '0.5rem', fontSize: '0.875rem', color: '#dc2626' }} role="alert">
                  {errors.type}
                </p>
              )}
            </FilterGroup>

            {/* Rating (if rating type selected) */}
            {formData.type === 'rating' && (
              <FilterGroup style={{ marginBottom: '1.5rem' }}>
                <FilterLabel>Note générale *</FilterLabel>
                <div style={{ display: 'flex', gap: '0.5rem' }}>
                  {[1, 2, 3, 4, 5].map((star) => (
                    <button
                      key={star}
                      type="button"
                      onClick={() => handleChange('rating', star.toString())}
                      style={{
                        padding: '0.25rem',
                        borderRadius: '0.25rem',
                        transition: 'color 0.2s',
                        color: parseInt(formData.rating) >= star ? '#eab308' : '#d1d5db',
                        backgroundColor: 'transparent',
                        border: 'none',
                        cursor: 'pointer'
                      }}
                      aria-label={`Noter ${star} étoile${star > 1 ? 's' : ''}`}
                    >
                      <svg style={{ height: '2rem', width: '2rem' }} fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                      </svg>
                    </button>
                  ))}
                </div>
                {errors.rating && (
                  <p style={{ marginTop: '0.5rem', fontSize: '0.875rem', color: '#dc2626' }} role="alert">
                    {errors.rating}
                  </p>
                )}
              </FilterGroup>
            )}

            {/* Category */}
            <FilterGroup style={{ marginBottom: '1.5rem' }}>
              <Select
                label="Catégorie"
                value={formData.category}
                onChange={(value) => handleChange('category', value)}
                options={[
                  { value: '', label: 'Sélectionner une catégorie' },
                  { value: 'interface', label: 'Interface utilisateur' },
                  { value: 'performance', label: 'Performance' },
                  { value: 'content', label: 'Contenu' },
                  { value: 'accessibility', label: 'Accessibilité' },
                  { value: 'mobile', label: 'Version mobile' },
                  { value: 'search', label: 'Recherche' },
                  { value: 'other', label: 'Autre' }
                ]}
                placeholder="Choisissez une catégorie..."
              />
            </FilterGroup>

            {/* Personal information */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem', marginBottom: '1.5rem' }}>
              <FilterGroup>
                <Input
                  label="Nom complet"
                  type="text"
                  value={formData.name}
                  onChange={(value) => handleChange('name', value)}
                  error={errors.name}
                  required
                  placeholder="Votre nom"
                  autoComplete="name"
                />
              </FilterGroup>

              <FilterGroup>
                <Input
                  label="Adresse email"
                  type="email"
                  value={formData.email}
                  onChange={(value) => handleChange('email', value)}
                  error={errors.email}
                  required
                  placeholder="votre@email.com"
                  autoComplete="email"
                />
              </FilterGroup>
            </div>

            {/* Subject */}
            <FilterGroup style={{ marginBottom: '1.5rem' }}>
              <Input
                label="Sujet"
                type="text"
                value={formData.subject}
                onChange={(value) => handleChange('subject', value)}
                error={errors.subject}
                required
                placeholder="Résumé de votre commentaire"
                maxLength={100}
              />
            </FilterGroup>

            {/* Message */}
            <FilterGroup style={{ marginBottom: '1.5rem' }}>
              <TextArea
                label="Message détaillé"
                value={formData.message}
                onChange={(value) => handleChange('message', value)}
                error={errors.message}
                required
                placeholder="Décrivez en détail votre commentaire, suggestion ou problème..."
                rows={6}
                maxLength={2000}
              />
              <p style={{ marginTop: '0.25rem', fontSize: '0.75rem', color: '#6b7280' }}>
                {formData.message.length}/2000 caractères
              </p>
            </FilterGroup>

            {/* Priority (for bugs and questions) */}
            {(formData.type === 'bug' || formData.type === 'question') && (
              <FilterGroup style={{ marginBottom: '1.5rem' }}>
                <Select
                  label="Priorité"
                  value={formData.priority}
                  onChange={(value) => handleChange('priority', value)}
                  options={[
                    { value: 'low', label: 'Faible' },
                    { value: 'normal', label: 'Normale' },
                    { value: 'high', label: 'Élevée' },
                    { value: 'urgent', label: 'Urgente' }
                  ]}
                />
              </FilterGroup>
            )}

            {/* Technical information notice */}
            <div style={{
              marginBottom: '1.5rem',
              padding: '1rem',
              backgroundColor: '#eff6ff',
              border: '1px solid #bfdbfe',
              borderRadius: '0.375rem'
            }}>
              <div style={{ display: 'flex' }}>
                <svg style={{ height: '1.25rem', width: '1.25rem', color: '#60a5fa', marginTop: '0.125rem', marginRight: '0.75rem' }} fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                </svg>
                <div style={{ fontSize: '0.875rem', color: '#1d4ed8' }}>
                  <p style={{ fontWeight: '500', marginBottom: '0.25rem' }}>Informations techniques automatiques</p>
                  <p>
                    Pour nous aider à mieux comprendre votre problème, certaines informations techniques
                    (URL de la page, navigateur utilisé) seront automatiquement incluses avec votre message.
                  </p>
                </div>
              </div>
            </div>

            {/* Submit button */}
            <div style={{ display: 'flex', gap: '0.75rem' }}>
              <Button
                type="submit"
                variant="primary"
                disabled={submitFeedbackMutation.isLoading}
                style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}
              >
                {submitFeedbackMutation.isLoading ? (
                  <>
                    <Loading size="sm" style={{ marginRight: '0.5rem' }} />
                    Envoi en cours...
                  </>
                ) : (
                  <>
                    <svg style={{ height: '1rem', width: '1rem', marginRight: '0.5rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Envoyer le commentaire
                  </>
                )}
              </Button>

              <Button
                type="button"
                onClick={() => window.history.back()}
                variant="outline"
                disabled={submitFeedbackMutation.isLoading}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
              >
                <svg style={{ height: '1rem', width: '1rem', marginRight: '0.5rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Annuler
              </Button>
            </div>
          </form>
        </FiltersSection>

        {/* Help information */}
        <div style={{
          marginTop: '2rem',
          backgroundColor: '#f9fafb',
          borderRadius: '0.5rem',
          padding: '1.5rem'
        }}>
          <h3 style={{ fontSize: '1.125rem', fontWeight: '600', color: '#111827', marginBottom: '0.75rem' }}>
            Comment pouvons-nous vous aider ?
          </h3>
          <div style={{
            display: 'grid',
            gridTemplateColumns: '1fr 1fr',
            gap: '1rem',
            fontSize: '0.875rem',
            color: '#6b7280'
          }}>
            <div>
              <h4 style={{ fontWeight: '500', color: '#111827', marginBottom: '0.5rem' }}>🐛 Signaler un problème</h4>
              <p>Décrivez les étapes pour reproduire le problème et votre environnement (navigateur, système).</p>
            </div>
            <div>
              <h4 style={{ fontWeight: '500', color: '#111827', marginBottom: '0.5rem' }}>💡 Suggérer une amélioration</h4>
              <p>Partagez vos idées pour améliorer l'interface, les fonctionnalités ou l'expérience utilisateur.</p>
            </div>
            <div>
              <h4 style={{ fontWeight: '500', color: '#111827', marginBottom: '0.5rem' }}>⭐ Évaluer le site</h4>
              <p>Donnez votre avis général sur le site et aidez-nous à identifier les points forts et faibles.</p>
            </div>
            <div>
              <h4 style={{ fontWeight: '500', color: '#111827', marginBottom: '0.5rem' }}>❓ Poser une question</h4>
              <p>Besoin d'aide pour utiliser une fonctionnalité ou comprendre comment naviguer sur le site ?</p>
            </div>
          </div>
        </div>
      </div>
    </PageContainer>
  );
};

export default FeedbackPage;
